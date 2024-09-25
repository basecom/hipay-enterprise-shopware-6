<?php

declare(strict_types=1);

namespace Shopware\Core;

use Basecom\TemplateTheme\CustomTestBootstrapper;
use RuntimeException;

$jwtDir = 'var/test/jwt';

if (!file_exists($jwtDir) && !mkdir($jwtDir, 0770, true) && !is_dir($jwtDir)) {
    throw new \RuntimeException(sprintf('Directory "%s" was not created', $jwtDir));
}

// generate jwt pk
$key = openssl_pkey_new([
    'digest_alg' => 'aes256',
    'private_key_type' => \OPENSSL_KEYTYPE_RSA,
    'encrypt_key_cipher' => \OPENSSL_CIPHER_AES_256_CBC,
    'encrypt_key' => 'shopware',
]);

// export private key
$result = openssl_pkey_export_to_file($key, $jwtDir . '/private.pem');
if ($result === false) {
    throw new RuntimeException('Could not export private key to file');
}

// export public key
$keyData = openssl_pkey_get_details($key);
file_put_contents($jwtDir . '/public.pem', $keyData['key']);

chmod($jwtDir . '/private.pem', 0660);
chmod($jwtDir . '/public.pem', 0660);

(new CustomTestBootstrapper())
    ->setPlatformEmbedded(false)
    ->addCallingPlugin(__DIR__ . '/../composer.json')
    ->addActivePlugins('BasecomFixturePlugin', 'TemplateTheme')
    // Uncomment the following line if you forgot to install the dependencies and have to install your plugin to test
    // ->setForceInstallPlugins(true)
    ->bootstrap();
