<?php

namespace yeesoft\auth\widgets;


use Yii;
use yii\helpers\Html;
use yii\base\InvalidConfigException;
use yii\authclient\ClientInterface;
use yii\authclient\widgets\AuthChoice as BaseAuthChoice;
use yeesoft\auth\models\Auth;
use yeesoft\auth\assets\AuthChoiceAsset;

/**
 * @inheritdoc
 */
class AuthChoice extends BaseAuthChoice
{

    const DISPLAY_ALL = 0;
    const DISPLAY_AUTHORIZED = 1;
    const DISPLAY_NON_AUTHORIZED = 2;

    public $displayClients = self::DISPLAY_ALL;
    public $shortView = false;

    /**
     * @var bool indicates if popup window should be used instead of direct links.
     */
    public $popupMode = false;

    /**
     * Initializes the widget.
     */
    public function init()
    {
        $view = Yii::$app->getView();
        AuthChoiceAsset::register($view);
        parent::init();
    }

    /**
     * Outputs client auth link.
     * @param ClientInterface $client external auth client instance.
     * @param string $text link text, if not set - default value will be generated.
     * @param array $htmlOptions link HTML options.
     * @return string generated HTML.
     * @throws InvalidConfigException on wrong configuration.
     */
    public function clientLink($client, $text = null, array $htmlOptions = [])
    {
        if ($this->shortView) {
            return parent::clientLink($client);
        } elseif ($text === null) {
            return parent::clientLink($client, Yii::t('yee/auth', 'Sign in using {service}', ['service' => $client->getTitle()]));
        }
    }

    /**
     * Renders the main content, which includes all external services links.
     * @return string generated HTML.
     */
    protected function renderMainContent()
    {
        $items = [];
        foreach ($this->getClients() as $client) {
            if ($this->isClientVisible($client)) {
                $items[] = Html::tag('li', $this->clientLink($client));
            }
        }
        $options = ['class' => 'auth-clients'];

        if (!$this->shortView) {
            Html::addCssClass($options, 'auth-clients-list');
        }

        return Html::tag('ul', implode('', $items), $options);
    }

    /**
     * Checks whether to show link for a given external service.
     * @return boolean whether external service link should be displayed.
     */
    protected function isClientVisible($client)
    {
        $authorized = array_keys(Auth::getAuthorizedClients());
        return ($this->displayClients == self::DISPLAY_ALL || ($this->displayClients == self::DISPLAY_AUTHORIZED && in_array($client->getName(), $authorized)) || ($this->displayClients == self::DISPLAY_NON_AUTHORIZED && !in_array($client->getName(), $authorized)));
    }

}
