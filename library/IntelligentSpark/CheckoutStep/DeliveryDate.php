<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2016 Intelligent Spark
 *
 * @package    Isotope Custom Step "Delivery Date"
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace IntelligentSpark\CheckoutStep;

use Isotope\CheckoutStep\CheckoutStep;
use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Template;



class DeliveryDate extends CheckoutStep implements IsotopeCheckoutStep {

    protected $strTemplate = 'iso_checkout_delivery_date';

    protected $strFormId = 'iso_checkout_delivery_date';

    protected $strTable = 'tl_iso_product_collection';
    
    protected $strField = 'delivery_date';

    /**
     * Returns true if order conditions are defined
     * @return  bool
     */
    public function isAvailable()
    {
        $objOrder = Isotope::getCart()->getDraftOrder();
        
        $arrItems = $objOrder->getItems();

        foreach($arrItems as $objProduct) {

            if($objProduct->type==4 || $objProduct->type==5 || $objProduct->delivery_date=='1') {
                return true;
            }
        }

        return false;
    }

    public function isSkippable() {
        return false;
    }

    /**
     * Return true if the step has an error and forwarding should be cancelled
     * @return  bool
     */
    public function hasError() {
        return false;
    }

    /**
     * Return short name of current class (e.g. for CSS)
     * @return  string
     */
    public function getStepClass() {
        $strClass = get_parent_class($this);
        $strClass = substr($strClass, strrpos($strClass, '\\') + 1);

        return parent::getStepClass() . ' ' . standardize($strClass);
    }

    /**
     * Generate the checkout step
     * @return  string
     */
    public function generate()
    {
        $objTemplate = new Template($this->strTemplate);

        $arrAttributes = ['dateDirection'=>'gtToday','inputType'=>'calendar','eval'=>['required'=>true,'rgxp'=>'date', 'datepicker'=>true]];

        $varValue = null;

        $objWidget = new FormCalendarField(FormCalendarField::getAttributesFromDca($arrAttributes, $this->strField, $varValue, $this->strField, $this->strTable, $this));
        $objWidget->storeValues = true;

        if (\Input::post('FORM_SUBMIT') == $this->strFormId)
        {
            $objWidget->validate();
            $varValue = $objWidget->value;

            $rgxp = $arrAttributes['eval']['rgxp'];

            // Convert date formats into timestamps (check the eval setting first -> #3063)
            if ($varValue != '' && in_array($rgxp, array('date', 'time', 'datim')))
            {
                try
                {
                    $objDate = new \Date($varValue, \Date::getFormatFromRgxp($rgxp));
                    $varValue = $objDate->tstamp;
                }
                catch (\OutOfBoundsException $e)
                {
                    $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['invalidDate'], $varValue));
                }
            }

            // Do not submit the field if there are errors
            if ($objWidget->hasErrors())
            {
                $doNotSubmit = true;
            }
            elseif ($objWidget->submitInput())
            {
                $objOrder = Isotope::getCart()->getDraftOrder();
                
                // Store the form data
                $_SESSION['FORM_DATA'][$this->strField] = $varValue;

                // Set the correct empty value (see #6284, #6373)
                if ($varValue === '')
                {
                    $varValue = $objWidget->getEmptyValue();
                }

                // Set the new value
                if ($varValue !== $objOrder->{$this->strField})
                {
                    $objOrder->{$this->strField};
                }
            }
        }

        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['delivery_date'];
        $objTemplate->datePicker = $objWidget->parse();

        return $objTemplate->parse();
    }


    /**
     * Get review information about this step
     * @return  array
     */
    public function review() {

        System::loadLanguageFile($this->strTable);

        $draftOrder = Isotope::getCart()->getDraftOrder();

        return array('delivery_date' => array
        (
            'headline' => $GLOBALS['TL_LANG']['tl_iso_product_collection']['delivery_date'][0],
            'info'     => $draftOrder->{$this->strField},
            'edit'     => Checkout::generateUrlForStep('review')
        ));
    }
    /**
     * Return array of tokens for notification
     * @param   IsotopeProductCollection
     * @return  array
     */
    public function getNotificationTokens(IsotopeProductCollection $objCollection)
    {


        return [$this->strField=>$objCollection->{$this->strField}];
    }

}