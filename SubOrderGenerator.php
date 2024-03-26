<?php

namespace SubOrderGenerator;

use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\Finder\Finder;
use Thelia\Core\Translation\Translator;
use Thelia\Install\Database;
use Thelia\Model\LangQuery;
use Thelia\Model\Message;
use Thelia\Model\MessageQuery;
use Thelia\Module\BaseModule;

class SubOrderGenerator extends BaseModule
{
    /** @var string */
    const DOMAIN_NAME = 'subordergenerator';
    const SUBORDER_LINK_MESSAGE_NAME = 'sub_order_link';

    const MESSAGES = [
        '1.0.0' =>[
            'name' => self::SUBORDER_LINK_MESSAGE_NAME,
            'subject' => 'subOrder subject',
            'title' => 'subOrder title'
        ],
    ];

    public static function generateEmailMessage()
    {
        foreach (self::MESSAGES as $message) {
            self::createMessageIfNotExist(
                $message['name'],
                $message['subject'],
                $message['title']
            );
        }
    }

    public static function createMessageIfNotExist($messageName, $subject, $title)
    {
        if (null === MessageQuery::create()->findOneByName($messageName)) {
            $message = new Message();
            $message
                ->setName($messageName)
                ->setHtmlTemplateFileName($messageName.'.html')
                ->setHtmlLayoutFileName('')
                ->setTextTemplateFileName($messageName.'.txt')
                ->setTextLayoutFileName('')
                ->setSecured(0);

            $languages = LangQuery::create()->find();

            foreach ($languages as $language) {
                $locale = $language->getLocale();

                $message->setLocale($locale);

                $message->setSubject(
                    Translator::getInstance()->trans($subject, [], $locale)
                );
                $message->setTitle(
                    Translator::getInstance()->trans($title, [], $locale)
                );
            }

            $message->save();
        }
    }

    public function postActivation(ConnectionInterface $con = null): void
    {
        $database = new Database($con);

        if (!self::getConfigValue('is_initialized', false)) {
            $database->insertSql(null, [__DIR__ . "/Config/TheliaMain.sql"]);
            self::setConfigValue('is_initialized', true);
        }

        self::generateEmailMessage();
    }
    /**
     * Defines how services are loaded in your modules
     *
     * @param ServicesConfigurator $servicesConfigurator
     */
    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([THELIA_MODULE_DIR . ucfirst(self::getModuleCode()). "/I18n/*"])
            ->autowire(true)
            ->autoconfigure(true);
    }

    /**
     * Execute sql files in Config/update/ folder named with module version (ex: 1.0.1.sql).
     *
     * @param $currentVersion
     * @param $newVersion
     * @param ConnectionInterface $con
     */
    public function update($currentVersion, $newVersion, ConnectionInterface $con = null): void
    {
        $finder = Finder::create()
            ->name('*.sql')
            ->depth(0)
            ->sortByName()
            ->in(__DIR__ . DS . 'Config' . DS . 'update');

        $database = new Database($con);

        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            if (version_compare($currentVersion, $file->getBasename('.sql'), '<')) {
                $database->insertSql(null, [$file->getPathname()]);
            }
        }

        foreach (self::MESSAGES as $version => $message) {
            if (version_compare($currentVersion, $version, '<')) {
                self::createMessageIfNotExist(
                    $message['name'],
                    $message['subject'],
                    $message['title']
                );
            }
        }
    }
}
