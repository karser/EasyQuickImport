<?php declare(strict_types=1);

namespace App\Controller;

use App\QuickbooksServer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class QbwcController
{
    private $server;

    public function __construct(QuickbooksServer $server)
    {
        $this->server = $server;
    }

    public function server(Request $request, string $appName): Response
    {
        $content = $request->getContent();
        $isXml = $request->getMethod() === 'POST' || $request->query->has('wsdl') || $request->query->has('WSDL');
        $contentType = $isXml ? 'text/xml' : 'text/plain';
        if ($isXml) {
            $output = $this->server->qbwc($content);
        } else {
            $output = $appName;
        }
        return new Response($output, 200, ['Content-Type' => $contentType]);
    }
}
