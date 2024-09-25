<?php

declare(strict_types=1);

namespace Basecom\TemplateTheme;

use Composer\Autoload\ClassLoader;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpKernel\KernelInterface;

class CustomTestBootstrapper
{
    private ?ClassLoader $classLoader = null;

    private ?string $projectDir = null;

    private ?string $databaseUrl = null;

    private ?bool $forceInstall = null;

    private bool $forceInstallPlugins = false;

    private bool $platformEmbedded = true;

    private bool $autoloadTestsDir = true;

    private bool $loadEnvFile = true;

    private ?OutputInterface $output = null;

    /**
     * @var string[]
     */
    private array $activePlugins = [];

    public function bootstrap(): self
    {
        $_SERVER['PROJECT_ROOT'] = $_ENV['PROJECT_ROOT'] = $this->getProjectDir();
        if (!\defined('TEST_PROJECT_DIR')) {
            \define('TEST_PROJECT_DIR', $_SERVER['PROJECT_ROOT']);
        }

        $classLoader = $this->getClassLoader();

        if ($this->autoloadTestsDir) {
            $classLoader->addPsr4(__NAMESPACE__.'\Tests\\', __DIR__.'/../tests');
        }

        if ($this->loadEnvFile) {
            $this->loadEnvFile();
        }

        $_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'] = $this->getDatabaseUrl();

        KernelLifecycleManager::prepare($classLoader);

        if ($this->isForceInstall() || !$this->dbExists()) {
            $this->install();

            if (!empty($this->activePlugins)) {
                $this->installPlugins();
            }
        } elseif ($this->forceInstallPlugins) {
            $this->installPlugins();
        }

        return $this;
    }

    public function getClassLoader(): ClassLoader
    {
        if ($this->classLoader !== null) {
            return $this->classLoader;
        }

        return $this->classLoader = require $this->getProjectDir().'/vendor/autoload.php';
    }

    public function getProjectDir(): string
    {
        if ($this->projectDir !== null) {
            return $this->projectDir;
        }

        if (isset($_SERVER['PROJECT_ROOT']) && file_exists($_SERVER['PROJECT_ROOT'])) {
            return $this->projectDir = $_SERVER['PROJECT_ROOT'];
        }

        if (isset($_ENV['PROJECT_ROOT']) && file_exists($_ENV['PROJECT_ROOT'])) {
            return $this->projectDir = $_ENV['PROJECT_ROOT'];
        }

        // only test cwd if it's not platform embedded (custom/plugins)
        if (!$this->platformEmbedded && file_exists('vendor')) {
            return $this->projectDir = (string) getcwd();
        }

        $dir = $rootDir = __DIR__;
        while (!file_exists($dir.'/vendor')) {
            if ($dir === \dirname($dir)) {
                return $rootDir;
            }
            $dir = \dirname($dir);
        }

        return $this->projectDir = $dir;
    }

    public function getDatabaseUrl(): string
    {
        if ($this->databaseUrl !== null) {
            return $this->databaseUrl;
        }

        $dbUrlParts = parse_url($_SERVER['DATABASE_URL'] ?? '') ?: [];

        $testToken          = getenv('TEST_TOKEN');
        $dbUrlParts['path'] = ($dbUrlParts['path'] ?? 'root').'_'.($testToken ?: 'test');

        $auth = isset($dbUrlParts['user']) ? ($dbUrlParts['user'].(isset($dbUrlParts['pass']) ? (':'.$dbUrlParts['pass']) : '').'@') : '';

        return $this->databaseUrl = \sprintf(
            '%s://%s%s%s%s%s',
            $dbUrlParts['scheme'] ?? 'mysql',
            $auth,
            $dbUrlParts['host'] ?? 'localhost',
            isset($dbUrlParts['port']) ? (':'.$dbUrlParts['port']) : '',
            $dbUrlParts['path'],
            isset($dbUrlParts['query']) ? ('?'.$dbUrlParts['query']) : '',
        );
    }

    public function setProjectDir(?string $projectDir): self
    {
        $this->projectDir = $projectDir;

        return $this;
    }

    public function setClassLoader(ClassLoader $classLoader): self
    {
        $this->classLoader = $classLoader;

        return $this;
    }

    public function setForceInstall(bool $forceInstall): self
    {
        $this->forceInstall = $forceInstall;

        return $this;
    }

    public function addActivePlugins(string ...$activePlugins): self
    {
        $this->activePlugins = $activePlugins;

        return $this;
    }

    /**
     * @param string|null $pathToComposerJson The composer.json to determine the plugin name. In most cases it's possible to find it automatically.
     *
     * Adds the calling plugin to the plugin list that is installed and activated
     */
    public function addCallingPlugin(?string $pathToComposerJson = null): self
    {
        if (!$pathToComposerJson) {
            $trace      = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
            $callerFile = $trace[0]['file'];

            $dir = \dirname($callerFile);
            $max = 10;
            while ($max-- > 0 && !file_exists($dir.'/composer.json')) {
                $dir = \dirname($dir);
            }

            if ($max <= 0) {
                throw new \RuntimeException(\sprintf('Failed to find plugin "composer.json". Starting point: %s', $callerFile));
            }

            $pathToComposerJson = $dir.'/composer.json';
        }

        if (!file_exists($pathToComposerJson)) {
            throw new \RuntimeException(\sprintf('Could not auto detect plugin name via "composer.json". Path: %s', $pathToComposerJson));
        }

        $composer  = json_decode((string) file_get_contents($pathToComposerJson), true);
        $baseClass = $composer['extra']['shopware-plugin-class'] ?? '';
        if ($baseClass === '') {
            throw new \RuntimeException(\sprintf('The "composer.json" does not contain "%s". Path: %s', 'extra.shopware-plugin-class', $pathToComposerJson));
        }

        $parts      = explode('\\', $baseClass);
        $pluginName = end($parts);

        $this->addActivePlugins($pluginName);

        return $this;
    }

    public function setPlatformEmbedded(bool $platformEmbedded): self
    {
        $this->platformEmbedded = $platformEmbedded;

        return $this;
    }

    public function setAutoloadTestsDir(bool $autoloadTestsDir): self
    {
        $this->autoloadTestsDir = $autoloadTestsDir;

        return $this;
    }

    public function setLoadEnvFile(bool $loadEnvFile): self
    {
        $this->loadEnvFile = $loadEnvFile;

        return $this;
    }

    public function setDatabaseUrl(?string $databaseUrl): self
    {
        $this->databaseUrl = $databaseUrl;

        return $this;
    }

    public function getOutput(): OutputInterface
    {
        if ($this->output !== null) {
            return $this->output;
        }

        return $this->output = new ConsoleOutput();
    }

    public function setOutput(?OutputInterface $output): self
    {
        $this->output = $output;

        return $this;
    }

    public function setForceInstallPlugins(bool $forceInstallPlugins): self
    {
        $this->forceInstallPlugins = $forceInstallPlugins;

        return $this;
    }

    public function isForceInstall(): bool
    {
        if ($this->forceInstall !== null) {
            return $this->forceInstall;
        }

        return $this->forceInstall = (bool) ($_SERVER['FORCE_INSTALL'] ?? false);
    }

    private function getKernel(): KernelInterface
    {
        return KernelLifecycleManager::getKernel();
    }

    private function getContainer(): ContainerInterface
    {
        return $this->getKernel()->getContainer();
    }

    private function dbExists(): bool
    {
        try {
            /** @var Connection $connection */
            $connection = $this->getContainer()->get(Connection::class);
            $connection->executeQuery('SELECT 1 FROM `plugin`')->fetchNumeric();

            return true;
        } catch (\Throwable $exists) {
            return false;
        }
    }

    private function loadEnvFile(): void
    {
        if (!class_exists(Dotenv::class)) {
            throw new \RuntimeException(\sprintf('The "%s" environment variable is not defined. You need to define environment variables for configuration or add "%s" as a composer dependency to load variables from a "%s" file(s).', 'APP_ENV', 'symfony/dotenv', '.env'));
        }

        $envFilePath = $this->getProjectDir().'/.env';
        if (file_exists($envFilePath) || file_exists($envFilePath.'.dist')) {
            (new Dotenv())->usePutenv()->loadEnv($envFilePath);
        }
    }

    /**
     * System install method, in order to force database refresh with basic-setup (no theme).
     */
    private function install(): void
    {
        $application    = new Application($this->getKernel());
        $installCommand = $application->find('system:install');

        $returnCode = $installCommand->run(
            new ArrayInput(
                [
                    '--create-database' => true,
                    '--force'           => true,
                    '--drop-database'   => true,
                    '--basic-setup'     => true,
                    '--no-assign-theme' => true,
                ],
                $installCommand->getDefinition()
            ),
            $this->getOutput()
        );

        if ($returnCode !== 0) {
            throw new \RuntimeException(\sprintf('The "%s" command failed with return code: %d', 'system:install', $returnCode));
        }

        // create new kernel after install
        KernelLifecycleManager::bootKernel(false);
    }

    /**
     * Modified plugin install method, in order to guarantee plugin installation in provided order.
     */
    private function installPlugins(): void
    {
        $application    = new Application($this->getKernel());
        $refreshCommand = $application->find('plugin:refresh');
        $refreshCommand->run(new ArrayInput([], $refreshCommand->getDefinition()), $this->getOutput());

        foreach ($this->activePlugins as $activePlugin) {
            $kernel = KernelLifecycleManager::bootKernel();

            $application    = new Application($kernel);
            $installCommand = $application->find('plugin:install');

            $args = [
                '--activate'  => true,
                '--reinstall' => true,
                'plugins'     => [$activePlugin],
            ];

            $returnCode = $installCommand->run(
                new ArrayInput($args, $installCommand->getDefinition()),
                $this->getOutput()
            );

            if ($returnCode !== 0) {
                throw new \RuntimeException(\sprintf('The "%s" command failed on plugin "%s" with return code: %d', 'plugin:install', $activePlugin, $returnCode));
            }
        }

        // create new kernel after install
        KernelLifecycleManager::bootKernel();
    }
}
