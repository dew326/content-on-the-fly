<?php

namespace EzSystems\EzContentOnTheFlyBundle\Controller;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Server\Values;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LocationController extends Controller
{
    protected $configResolver;
    
    protected $locationService;

    public function __construct(ConfigResolverInterface $configResolver, LocationService $locationService)
    {
        $this->configResolver = $configResolver;
        $this->locationService = $locationService;
    }

    public function suggested(Request $request, $content)
    {
        $response = new JsonResponse();
        
        $configuration = $this->configResolver->getParameter('content', 'ez_contentonthefly');

        if (!isset($configuration[$content]) && $content != 'default') {
            $content = 'default';
        }
        
        if (isset($configuration[$content])) {
            $locations = $configuration[$content]['location'];
        }
        else {
            $locations = [];
        }
        
        $suggested = [];
        foreach ($locations as $locationId) {
            $location = $this->locationService->loadLocation($locationId);
            $suggested[] = new Values\RestLocation(
                $location,
                $this->locationService->getLocationChildCount($location)
            );
        }

        return new Values\LocationList($suggested, $request->getPathInfo());
    }
}