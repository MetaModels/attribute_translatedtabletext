<?php

/**
 * This file is part of MetaModels/attribute_translatedtabletext.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_translatedtabletext
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_translatedtabletext/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTranslatedTableTextBundle\EventListener\DcGeneral\Table;

use Contao\StringUtil;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminatorAwareTrait;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the helper class for handling translated table text fields.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackendTableListener
{
    use RequestScopeDeterminatorAwareTrait;

    /**
     * Metamodel factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * Event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * BackendTableListener constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator Request scope determinator.
     * @param IFactory                 $factory           Metamodel factory.
     * @param EventDispatcherInterface $eventDispatcher   Event dispatcher.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IFactory $factory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->setScopeDeterminator($scopeDeterminator);

        $this->factory         = $factory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Populate the extra data of the widget.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function fillExtraData(BuildWidgetEvent $event)
    {
        if (!$this->scopeDeterminator?->currentScopeIsBackend()) {
            return;
        }

        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if (
            ($dataDefinition->getName() !== 'tl_metamodel_attribute')
            || ($event->getProperty()->getName() !== 'translatedtabletext_cols')
        ) {
            return;
        }

        $model         = $event->getModel();
        $metaModelName = $this->factory->translateIdToMetaModelName($event->getModel()->getProperty('pid'));
        $objMetaModel  = $this->factory->getMetaModel($metaModelName);
        $translator    = $event->getEnvironment()->getTranslator();
        assert($translator instanceof TranslatorInterface);

        // Check model and input for the cols and get the max value.
        $intModelCols  = $model->getProperty('tabletext_quantity_cols');
        $inputProvider = $event->getEnvironment()->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);
        $intInputCols = $inputProvider->getValue('tabletext_quantity_cols');
        $intCols      = \max((int) $intModelCols, (int) $intInputCols);

        // For new models, we might not have a value.
        if (!$intCols) {
            return;
        }

        /** @psalm-suppress DeprecatedMethod */
        if (!($objMetaModel && $objMetaModel->isTranslated())) {
            return;
        }

        $attribute = $objMetaModel->getAttributeById((int) $model->getProperty('id'));
        $arrValues = $attribute ? StringUtil::deserialize($attribute->get('name')) : [];
        assert(\is_array($arrValues));
        /** @var array<string, string> $arrValues */

        $languageEvent = new LoadLanguageFileEvent('languages');
        $this->eventDispatcher->dispatch($languageEvent, ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE);

        $arrLanguages = [];
        /** @psalm-suppress DeprecatedMethod */
        foreach ((array) $objMetaModel->getAvailableLanguages() as $strLangCode) {
            $arrLanguages[$strLangCode] = $translator->translate($strLangCode, 'LNG');
        }
        \asort($arrLanguages);

        // Ensure we have the values present.
        if (empty($arrValues)) {
            /** @psalm-suppress DeprecatedMethod */
            foreach ((array) $objMetaModel->getAvailableLanguages() as $strLangCode) {
                $arrValues[$strLangCode] = '';
            }
        }

        $arrRowClasses = [];
        foreach (\array_keys($arrValues) as $strLangcode) {
            /** @psalm-suppress DeprecatedMethod */
            $arrRowClasses[] = ($strLangcode === $objMetaModel->getFallbackLanguage())
                ? 'fallback_language'
                : 'normal_language';
        }

        $data                                      = $event->getProperty()->getExtra();
        $data['minCount']                          = \count($arrLanguages);
        $data['maxCount']                          = \count($arrLanguages);
        $data['columnFields']['langcode']['label'] = $translator->translate(
            'name_langcode',
            'tl_metamodel_attribute'
        );

        $data['columnFields']['langcode']['options']            = $arrLanguages;
        $data['columnFields']['langcode']['eval']['rowClasses'] = $arrRowClasses;
        $data['columnFields']['rowLabels']['label']             = $translator->translate(
            'tabletext_rowLabels.0',
            'tl_metamodel_attribute'
        );

        $data['columnFields']['rowLabels']['eval']['minCount']                          = $intCols;
        $data['columnFields']['rowLabels']['eval']['maxCount']                          = $intCols;
        $data['columnFields']['rowLabels']['eval']['columnFields']['rowLabel']['label'] = $translator->translate(
            'tabletext_rowLabel.0',
            'tl_metamodel_attribute'
        );

        $data['columnFields']['rowLabels']['eval']['columnFields']['rowStyle']['label'] = $translator->translate(
            'tabletext_rowStyle.0',
            'tl_metamodel_attribute'
        );

        $event->getProperty()->setExtra($data);
    }

    /**
     * Decode the values into a real table array.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function loadValues(DecodePropertyValueForWidgetEvent $event)
    {
        if (!$this->scopeDeterminator?->currentScopeIsBackend()) {
            return;
        }

        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);
        $inputProvider = $event->getEnvironment()->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        if (
            ($dataDefinition->getName() !== 'tl_metamodel_attribute')
            || ($event->getProperty() !== 'translatedtabletext_cols')
            || ($inputProvider->getParameter('act') === 'select'
                && !$event->getModel()->getId())
        ) {
            return;
        }

        $metaModelName = $this->factory->translateIdToMetaModelName($event->getModel()->getProperty('pid'));
        $metaModel     = $this->factory->getMetaModel($metaModelName);
        assert($metaModel instanceof IMetaModel);

        // Check model and input for the cols and get the max value.
        $intModelCols = $event->getModel()->getProperty('tabletext_quantity_cols');
        $intInputCols = $inputProvider->getValue('tabletext_quantity_cols');
        $intCols      = \max((int) $intModelCols, (int) $intInputCols);
        $varValue     = StringUtil::deserialize($event->getValue());

        // Kick unused lines.
        foreach ((array) $varValue as $strLanguage => $arrRows) {
            if (\count((array) $arrRows) > $intCols) {
                $varValue[$strLanguage] = \array_slice($varValue[$strLanguage], 0, $intCols);
            }
        }

        /**
         * @psalm-suppress DeprecatedMethod
         * @psalm-suppress TooManyArguments
         */
        if (!($metaModel instanceof ITranslatedMetaModel) && !$metaModel->isTranslated(false)) {
            // If we have an array, return the first value and exit, if not an array, return the value itself.
            if (\is_array($varValue)) {
                $event->setValue($varValue[\key($varValue)]);
            } else {
                $event->setValue($varValue);
            }

            return;
        }

        // Sort like in MetaModel definition.
        /** @psalm-suppress DeprecatedMethod */
        $arrLanguages  = $metaModel->getAvailableLanguages();
        $arrOutput     = [];

        if (null !== $arrLanguages) {
            foreach ($arrLanguages as $strLangCode) {
                if (\is_array($varValue)) {
                    $varSubValue = $varValue[$strLangCode] ?? '';
                } else {
                    $varSubValue = $varValue;
                }

                if (\is_array($varSubValue)) {
                    $arrOutput[] = ['langcode' => $strLangCode, 'rowLabels' => $varSubValue];
                } else {
                    $arrOutput[] = ['langcode' => $strLangCode, 'value' => $varSubValue];
                }
            }
        }

        $event->setValue(\serialize($arrOutput));
    }

    /**
     * Encode the values into a serialized array.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function saveValues(EncodePropertyValueFromWidgetEvent $event)
    {
        if (!$this->scopeDeterminator?->currentScopeIsBackend()) {
            return;
        }

        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);
        if (
            ($dataDefinition->getName() !== 'tl_metamodel_attribute')
            || ($event->getProperty() !== 'translatedtabletext_cols')
        ) {
            return;
        }

        $metaModelName = $this->factory->translateIdToMetaModelName($event->getModel()->getProperty('pid'));
        $objMetaModel  = $this->factory->getMetaModel($metaModelName);
        assert($objMetaModel instanceof IMetaModel);
        $varValue = $event->getValue();

        // Not translated, make it a plain string.
        /** @psalm-suppress DeprecatedMethod */
        if (!$objMetaModel->isTranslated()) {
            $event->setValue(\serialize($varValue));

            return;
        }

        $arrLangValues = StringUtil::deserialize($varValue);
        $arrOutput     = [];

        foreach ($arrLangValues as $varSubValue) {
            $strLangCode = $varSubValue['langcode'];
            unset($varSubValue['langcode']);
            if (\count($varSubValue) > 1) {
                $arrOutput[$strLangCode] = $varSubValue;
            } else {
                $arrKeys = \array_keys($varSubValue);

                $arrOutput[$strLangCode] = $varSubValue[$arrKeys[0]];
            }
        }

        $event->setValue(\serialize($arrOutput));
    }
}
