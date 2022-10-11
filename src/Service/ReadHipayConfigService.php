<?php

namespace HiPay\Payment\Service;

use HiPay\Fullservice\HTTP\Configuration\Configuration;
use HiPay\Payment\HiPayPaymentPlugin;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ReadHipayConfigService
{
    private SystemConfigService $configHipay;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->configHipay = $systemConfigService;
    }

    /**
     * Get the environment name.
     */
    public function getEnvironment(): string
    {
        return (string) $this->configHipay->getString(HiPayPaymentPlugin::getModuleName().'.config.environment');
    }

    /**
     * Check if the environment is on prod.
     */
    public function isProdActivated(): bool
    {
        return Configuration::API_ENV_PRODUCTION === strtolower($this->getEnvironment());
    }

    /**
     * Check if the environment is on test.
     */
    public function isTestActivated(): bool
    {
        return !$this->isProdActivated();
    }

    /**
     * Get the private login based on the environment.
     */
    public function getPrivateLogin(): string
    {
        $key = $this->isProdActivated() ? 'privateLoginProduction' : 'privateLoginStage';

        return $this->configHipay->getString($this->getConfigPrefix().$key);
    }

    /**
     * Get the private password based on the environment.
     */
    public function getPrivatePassword(): string
    {
        $key = $this->isProdActivated() ? 'privatePasswordProduction' : 'privatePasswordStage';

        return $this->configHipay->getString($this->getConfigPrefix().$key);
    }

    /**
     * Get the public login based on the environment.
     */
    public function getPublicLogin(): string
    {
        $key = $this->isProdActivated() ? 'publicLoginProduction' : 'publicLoginStage';

        return $this->configHipay->getString($this->getConfigPrefix().$key);
    }

    /**
     * Get the public password based on the environment.
     */
    public function getPublicPassword(): string
    {
        $key = $this->isProdActivated() ? 'publicPasswordProduction' : 'publicPasswordStage';

        return $this->configHipay->getString($this->getConfigPrefix().$key);
    }

    /**
     * Get the hash based on the environment.
     */
    public function getHash(): string
    {
        $key = $this->isProdActivated() ? 'hashProduction' : 'hashStage';

        return $this->configHipay->getString($this->getConfigPrefix().$key);
    }

    /**
     * Get the passphrase based on the environment.
     */
    public function getPassphrase(): string
    {
        $key = $this->isProdActivated() ? 'passphraseProduction' : 'passphraseStage';

        return $this->configHipay->getString($this->getConfigPrefix().$key);
    }

    /**
     * Get the capture mode name.
     */
    public function getCaptureMode(): string
    {
        return $this->configHipay->getString(HiPayPaymentPlugin::getModuleName().'.config.captureMode');
    }

    /**
     * check if the capture mode is manual.
     */
    public function isCaptureManual(): bool
    {
        return 'manual' === $this->getCaptureMode();
    }

    /**
     * check if the capture mode is auto.
     */
    public function isCaptureAuto(): bool
    {
        return !$this->isCaptureManual();
    }

    /**
     * Get the operation mode name.
     */
    public function getOperationMode(): string
    {
        return $this->configHipay->getString(HiPayPaymentPlugin::getModuleName().'.config.operationMode');
    }

    /**
     * Check if the operation mode is hosted fields.
     */
    public function isHostedFields(): bool
    {
        return 'hostedFields' === $this->getOperationMode();
    }

    /**
     * Check if the operation mode is hosted page.
     */
    public function isHostedPage(): bool
    {
        return 'hostedPage' === $this->getOperationMode();
    }

    /**
     * Check if the one click paiement is activated.
     */
    public function isOneClickPayment(): bool
    {
        return $this->configHipay->getBool(HiPayPaymentPlugin::getModuleName().'.config.oneClickPayment');
    }

    /**
     * Check if cart remembering is activated.
     */
    public function isRememberCart(): bool
    {
        return $this->configHipay->getBool(HiPayPaymentPlugin::getModuleName().'.config.rememberCart');
    }

    /**
     * Check if debug mode is activated.
     */
    public function isDebugMode(): bool
    {
        return $this->configHipay->getBool(HiPayPaymentPlugin::getModuleName().'.config.debugMode');
    }

    /**
     * get the 3-D Secure authenticator flag.
     */
    public function get3DSAuthenticator(): int
    {
        try {
            return $this->configHipay->getInt(HiPayPaymentPlugin::getModuleName().'.config.authFlag3DS');
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Check if debug mode is activated.
     *
     * @return array<string,mixed>
     */
    public function getCustomStyle(): array
    {
        $styles = [];

        if ($this->isHostedFields()) {
            $fields = [
                'hostedFieldsTextColor',
                'hostedFieldsFontFamilly',
                'hostedFieldsFontSize',
                'hostedFieldsFontWeight',
                'hostedFieldsPlaceholderColor',
                'hostedFieldsCaretColor',
                'hostedFieldsIconColor',
            ];

            foreach ($fields as $field) {
                if ($value = $this->configHipay->get($this->getConfigPrefix().$field)) {
                    $styles[$field] = $value;
                }
            }
        }

        return $styles;
    }

    /**
     * Get the prefix key of plugin configuration.
     */
    private function getConfigPrefix(): string
    {
        return HiPayPaymentPlugin::getModuleName().'.config.';
    }
}
