<?php
/**
 * Test: IPub\Gravatar\Gravatar
 * @testCase
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Gravatar!
 * @subpackage     Tests
 * @since          1.0.0
 *
 * @date           15.01.17
 */

declare(strict_types = 1);

namespace IPubTests\Gravatar;

use Nette;
use Nette\Application;
use Nette\Application\Routers;
use Nette\Application\UI;
use Nette\Utils;

use Tester;
use Tester\Assert;

use IPub\Gravatar;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require __DIR__ . DS . 'libs' . DS . 'RouterFactory.php';

class TemplateTest extends Tester\TestCase
{
	/**
	 * @var Application\IPresenterFactory
	 */
	private $presenterFactory;

	/**
	 * @var Nette\DI\Container
	 */
	private $container;

	/**
	 * @var Gravatar\Gravatar
	 */
	private $gravatar;

	/**
	 * {@inheritdoc}
	 */
	public function setUp() : void
	{
		parent::setUp();

		$this->container = $this->createContainer();

		// Get presenter factory from container
		$this->presenterFactory = $this->container->getByType(Application\IPresenterFactory::class);

		// Get device view service
		$this->gravatar = $this->container->getByType(Gravatar\Gravatar::class);
	}

	public function testMacroVersion() : void
	{
		// Create test presenter
		$presenter = $this->createPresenter();

		// Create GET request
		$request = new Application\Request('Test', 'GET', ['action' => 'default']);
		// & fire presenter & catch response
		$response = $presenter->run($request);

		$dq = Tester\DomQuery::fromHtml((string) $response->getSource());

		Assert::true($dq->has('img[id*="nMacro"]'));
		Assert::true($dq->has('img[id*="normalMacro"]'));

		$nMacro = $dq->find('img[id*="nMacro"]');
		$nMacro = (string) $nMacro[0]->attributes()->{'src'};

		Assert::equal($this->gravatar->buildUrl('john@doe.com', 100), $nMacro);

		$nMacro = $dq->find('img[id*="normalMacro"]');
		$nMacro = (string) $nMacro[0]->attributes()->{'src'};

		Assert::equal($this->gravatar->buildUrl('john@doe.com', 100), $nMacro);
	}

	/**
	 * @return Application\IPresenter
	 */
	private function createPresenter() : Application\IPresenter
	{
		// Create test presenter
		$presenter = $this->presenterFactory->createPresenter('Test');
		// Disable auto canonicalize to prevent redirection
		$presenter->autoCanonicalize = FALSE;

		return $presenter;
	}

	/**
	 * @return Nette\DI\Container
	 */
	private function createContainer() : Nette\DI\Container
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		Gravatar\DI\GravatarExtension::register($config);

		$config->addConfig(__DIR__ . DS . 'files' . DS . 'config.neon');

		$version = getenv('NETTE');

		$config->addConfig(__DIR__ . DS . 'files' . DS . 'presenters.neon');

		return $config->createContainer();
	}
}

class TestPresenter extends UI\Presenter
{
	use Gravatar\TGravatar;

	public function renderDefault() : void
	{
		// Set template for component testing
		$this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'default.latte');
	}
}

\run(new TemplateTest());
