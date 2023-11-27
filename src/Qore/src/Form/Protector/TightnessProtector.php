<?php

namespace Qore\Form\Protector;

use Laminas\Session\Container;
use Psr\Http\Message\ServerRequestInterface;
use Qore\Application;
use Qore\Collection\Collection;
use Qore\Form\Field\Hidden;
use Qore\Form\FormManager;
use Qore\Qore;
use Qore\SessionManager\SessionManager;
use Ramsey\Uuid\Uuid;

/**
 * Class: TightnessProtector
 *
 */
class TightnessProtector implements ProtectorInterface
{
    const TOKEN_PARAM = '__fmtoken';

    /**
     * @var SessionManager
     */
    private SessionManager $_sm;

    private SessionManager $_session;

    private $_fm;

    /**
     * Constructor
     *
     * @param \Qore\SessionManager\SessionManager $_session
     */
    public function __construct(SessionManager $_session)
    {
        $this->_session = $_session;
    }

    /**
     * @inheritdoc
     */
    public function setFormManager(FormManager $_fm): ProtectorInterface
    {
        $this->_fm = $_fm;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function protect(ServerRequestInterface $_request) : void
    {
        $fields = new Collection($this->_fm->getFields());
        $fieldsNames = $fields->map(function($_field){
            return $_field->getName();
        })->toList();

        # - Save form state to session
        $container = $this->getContainer();

        $token = $this->getToken($_request);

        if (! isset($container[$token])) {
            if (count($container->getArrayCopy()) > 4) {
                $container->exchangeArray(array_slice($container->getArrayCopy(), -4));
            }
        }

        $container[$token] = array_unique(array_merge(
            $container[$token] ?? [],
            $fieldsNames
        ));

        /** Set form token */
        $this->_fm->setField(new Hidden(static::TOKEN_PARAM, [
            'data' => $token,
        ]));
    }

    /**
     * @inheritdoc
     */
    public function inspect(ServerRequestInterface $_request) : ServerRequestInterface
    {
        $container = $this->getContainer();

        $token = $this->getToken($_request);

        if (! isset($container[$token]) || ! $_request->getParsedBody()) {
            return $_request->withParsedBody([]);
        }

        $fieldsTempKeys = (new Collection(array_values($container[$token])))
            ->map(function($_value) {
                return '-' . sha1($_value) . '-';
            })->toList();

        $fieldsOriginKeys = (new Collection(array_values($container[$token])))
            ->map(function($_value) {
                return urlencode($_value);
            })->toList();

        $rawBody = str_replace($fieldsOriginKeys, $fieldsTempKeys, http_build_query($_request->getParsedBody()));

        $tempParsedBody = [];
        parse_str($rawBody, $tempParsedBody);

        $protectedParams = (new Collection($tempParsedBody))
            ->filter(function($_value, $_key) use ($fieldsTempKeys) {
                return in_array($_key, $fieldsTempKeys);
            })->toArray();

        $rawBody = str_replace($fieldsTempKeys, $fieldsOriginKeys, http_build_query($protectedParams));
        parse_str($rawBody, $protectedParams);

        unset($container[$token]);

        return $_request->withParsedBody($protectedParams);
    }

    /**
     * Get session container for current form
     *
     * @param ServerRequestInterface $_request
     */
    private function getContainer() : Container
    {
        $container = ($this->_session)($this->getContainerName());
        $container->setExpirationSeconds(1*60*60);

        return $container;
    }

    /**
     * Get container name
     *
     * @return string
     */
    private function getContainerName(): string
    {
        return sha1(static::class . $this->_fm->getName());
    }

    /**
     * Get or generate token
     *
     * @param \Psr\Http\Message\ServerRequestInterface $_request
     *
     * @return string
     */
    private function getToken(ServerRequestInterface $_request): string
    {
        $params = $_request->getParsedBody();
        return $params[static::TOKEN_PARAM] ?? Uuid::uuid4();
    }

}
