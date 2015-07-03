<?php

namespace yeesoft\auth\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;
use yii\authclient\ClientInterface;
use yii\authclient\widgets\AuthChoice as BaseAuthChoice;
use yii\authclient\widgets\AuthChoiceItem;

/**
 * @inheritdoc
 */
class AuthChoice extends BaseAuthChoice
{

    /**
     * Initializes the widget.
     */
    public function init()
    {
        $view = Yii::$app->getView();
        YeeAuthAsset::register($view);
        parent::init();
    }

    /**
     * Outputs client auth link.
     * @param ClientInterface $client external auth client instance.
     * @param string $text link text, if not set - default value will be generated.
     * @param array $htmlOptions link HTML options.
     * @throws InvalidConfigException on wrong configuration.
     */
    public function clientLink($client, $text = null, array $htmlOptions = [])
    {
        if ($text === null) {
            $text = Html::tag('span', $client->getTitle(),
                    ['class' => 'auth-title']);
        }
        if (!array_key_exists('class', $htmlOptions)) {
            $htmlOptions['class'] = $client->getName();
        }

        $viewOptions = $client->getViewOptions();
        if (empty($viewOptions['widget'])) {
            if ($this->popupMode) {
                if (isset($viewOptions['popupWidth'])) {
                    $htmlOptions['data-popup-width'] = $viewOptions['popupWidth'];
                }
                if (isset($viewOptions['popupHeight'])) {
                    $htmlOptions['data-popup-height'] = $viewOptions['popupHeight'];
                }
            }


            echo Html::a($text, $this->createClientUrl($client), $htmlOptions);
        } else {
            $widgetConfig = $viewOptions['widget'];
            if (!isset($widgetConfig['class'])) {
                throw new InvalidConfigException('Widget config "class" parameter is missing');
            }
            /* @var $widgetClass Widget */
            $widgetClass = $widgetConfig['class'];
            if (!(is_subclass_of($widgetClass, AuthChoiceItem::className()))) {
                throw new InvalidConfigException('Item widget class must be subclass of "'.AuthChoiceItem::className().'"');
            }
            unset($widgetConfig['class']);
            $widgetConfig['client']     = $client;
            $widgetConfig['authChoice'] = $this;
            echo $widgetClass::widget($widgetConfig);
        }
    }

    /**
     * Renders the main content, which includes all external services links.
     */
    protected function renderMainContent()
    {
        echo Html::beginTag('ul', ['class' => 'auth-clients clear']);
        foreach ($this->getClients() as $externalService) {
            echo Html::beginTag('li',
                ['class' => 'auth-client '.$externalService->getName()]);
            $this->clientLink($externalService);
            echo Html::endTag('li');
        }
        echo Html::endTag('ul');
    }
}