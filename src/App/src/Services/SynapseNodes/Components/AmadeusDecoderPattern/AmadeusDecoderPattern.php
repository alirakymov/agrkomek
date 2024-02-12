<?php

declare(strict_types=1);

namespace Qore\App\SynapseNodes\Components\AmadeusDecoderPattern;

use Hoa\Compiler\Llk\TreeNode;
use Qore\Qore;
use Qore\SynapseManager\Structure\Entity\SynapseBaseEntity;
use Throwable;

/**
 * Class: AmadeusDecoderPattern
 *
 * @see Qore\SynapseManager\Structure\Entity\SynapseBaseEntity
 */
class AmadeusDecoderPattern extends SynapseBaseEntity
{
    /**
     * Get regex
     *
     * @return string|null
     */
    public function getRegex(): ?string
    {
        if (! $this->regex) {
            return null;
        }

        return in_array(mb_substr($this->regex, 0, 1), ['/', '#', '~', '%', '@', ';', '`'])
            ? $this->regex
            : sprintf('/%s/mu', $this->regex);
    }

    /**
     * Collect groups
     *
     * @param \Hoa\Compiler\Llk\TreeNode $_ast 
     *
     * @return array 
     */
    public function collectGroups(TreeNode $_ast): array
    {
        $children = $_ast->getChildren();
        if (is_null($children)) {
            return [];
        }

        $result = [];
        foreach ($children as $child) {
            if ($child->isToken() && $child->getValueToken() == 'capturing_name') {
                $result[] = $child->getValueValue();
            }

            $result = array_merge($result, $this->collectGroups($child));
        }

        return $result;
    }

    /**
     * subscribe
     *
     */
    public static function subscribe()
    {
        static::after('initialize', $decode = function($_event) {
            $entity = $_event->getTarget();
            if ($entity->groups && is_string($entity->groups)) {
                $entity->groups = json_decode($entity->groups, true);
            }
        });

        static::before('save', function($_event) {
            try {
                $entity = $_event->getTarget();
                # - Parse regex
                $grammar  = new \Hoa\File\Read('hoa://Library/Regex/Grammar.pp');
                $compiler = \Hoa\Compiler\Llk\Llk::load($grammar);
                $ast = $compiler->parse($entity->regex);
                # - Initialize groups from regex
                $groups = [];
                $entityGroups = $entity->groups ?? [];
                $entityGroups = is_string($entityGroups) ? json_decode($entityGroups, true) : $entityGroups;
                # - Collect and save named groups
                foreach ($entity->collectGroups($ast) as $group) {
                    $groups[$group] = $entityGroups[$group] ?? null;
                }
                # - Save groups
                $entity->groups = $groups;
            } catch (Throwable $e) {
            }
        });

        static::before('save', function($_event) {
            $entity = $_event->getTarget();
            if (! is_null($entity->groups) && ! is_string($entity->groups)) {
                $entity->groups = json_encode($entity->groups, JSON_UNESCAPED_UNICODE);
            }

        });

        static::after('save', $decode);

        parent::subscribe();
    }

}
