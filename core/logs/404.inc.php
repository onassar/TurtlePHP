
*Invalid Request*
Host:               <?= ($_SERVER['HTTP_HOST'] ?? ':unknown:') . (PHP_EOL) ?>
Request Path:       <?= TurtlePHP\Application::getRequest()->getPath() . (PHP_EOL) ?>
Request URI:        <?= ($_SERVER['REQUEST_URI'] ?? ':unknown:') . (PHP_EOL) ?>
HTTP Referrer:      <?= ($_SERVER['HTTP_REFERER'] ?? ':unknown:') . (PHP_EOL) ?>
IP:                 <?= (IP) . (PHP_EOL) ?>
User Agent:         <?= ($_SERVER['HTTP_USER_AGENT'] ?? ':unknown:') . (PHP_EOL) ?>
Method:             <?= ($_SERVER['REQUEST_METHOD'] ?? ':unknown:') . (PHP_EOL) ?>
