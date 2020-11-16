<?php
declare(strict_types=1);

namespace AdamAveray\SalesforceUtils\Client;

use Phpforce\SoapClient\Soap\SoapClient;
use Phpforce\SoapClient\Soap\SoapClientFactory as BaseSoapClientFactory;

class SoapClientFactory extends BaseSoapClientFactory
{
    public function factory($wsdl, $extraSoapOptions = null)
    {
        if ($wsdl === null) {
            throw new \InvalidArgumentException('WSDL cannot be null');
        }

        $soapOptions = array_merge(
            [
                'trace' => 1,
                'features' => \SOAP_SINGLE_ELEMENT_ARRAYS,
                'classmap' => $this->classmap,
                'typemap' => $this->getTypeConverters()->getTypemap(),
            ],
            isset($extraSoapOptions) ? $extraSoapOptions : [],
        );

        return new SoapClient($wsdl, $soapOptions);
    }
}
