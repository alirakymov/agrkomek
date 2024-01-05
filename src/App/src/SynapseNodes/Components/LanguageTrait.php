<?php

namespace Qore\App\SynapseNodes\Components;

trait LanguageTrait
{
    /**
     * Get languages
     *
     * @return array
     */
    public static function getLanguages(): array
    {
        return [
            ['id' => 'kaz', 'label' => 'Казахский'],
            ['id' => 'rus', 'label' => 'Русский'],
        ];
    }

}
