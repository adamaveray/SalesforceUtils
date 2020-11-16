<?php
declare(strict_types=1);

namespace AdamAveray\SalesforceUtils\Client;

use Phpforce\SoapClient\Plugin\LogPlugin;
use Psr\Log\LoggerInterface;

class ClientBuilder
{
    /** @var string $wsdl */
    protected $wsdl;
    /** @var string $username */
    protected $username;
    /** @var string $password */
    protected $password;
    /** @var string|null $token */
    protected $token;
    /** @var array $soapOptions */
    protected $soapOptions;
    /** @var LoggerInterface|null $log */
    protected $log;

    /**
     * @param string $wsdl       The path to the WSDL file to use
     * @param string $username   A Salesforce username
     * @param string $password   The password for the given user
     * @param null|string $token The security token for the given user
     * @param array $soapOptions Options to pass to the SoapClient
     */
    public function __construct(
        string $wsdl,
        string $username,
        string $password,
        ?string $token = null,
        array $soapOptions = []
    ) {
        $this->wsdl = $wsdl;
        $this->username = $username;
        $this->password = $password;
        $this->token = $token;
        $this->soapOptions = array_merge(
            [
                'exceptions' => true,
            ],
            $soapOptions,
        );
    }

    /**
     * Enable logging on the generated Client
     *
     * @param LoggerInterface $log
     * @return $this
     */
    public function withLog(LoggerInterface $log)
    {
        $this->log = $log;
        return $this;
    }

    /**
     * @return Client
     */
    public function build()
    {
        $soapClientFactory = new SoapClientFactory();
        $soapClient = $soapClientFactory->factory(
            $this->wsdl,
            $this->soapOptions,
        );

        $client = new Client(
            $soapClient,
            $this->username,
            $this->password,
            $this->token,
        );

        if ($this->log !== null) {
            $logPlugin = new LogPlugin($this->log);
            $client->getEventDispatcher()->addSubscriber($logPlugin);
        }

        return $client;
    }
}
