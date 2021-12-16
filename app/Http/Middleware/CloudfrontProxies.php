<?php


namespace App\Http\Middleware;


use Carbon\Carbon;
use Closure;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CloudfrontProxies
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->header('cloudfront-forwarded-proto')) {
            $this->loadTrustedProxies($request);
            $this->setCloudfrontHeaders($request);
        }
        return $next($request);
    }

    /**
     * If this is a cloudfront request, load up the trusted proxies
     */
    protected function loadTrustedProxies($request)
    {
        // Get the CloudFront IP addresses
        $proxies = Cache::remember('cloudfront-proxy-ip-addresses', Carbon::now()->addHour(), function () {
            $ip_range_data = 'https://ip-ranges.amazonaws.com/ip-ranges.json';
            $client = app()->make(Guzzle::class);
            $res = $client->get($ip_range_data);
            $data = collect(json_decode($res->getBody())->prefixes)
                ->filter(function ($address) {
                    return $address->service == 'CLOUDFRONT';
                })
                ->pluck('ip_prefix');
            return $data->toArray();
        });
        $request->setTrustedProxies(array_merge($request->getTrustedProxies(), $proxies), Request::HEADER_X_FORWARDED_ALL);
    }

    protected function setCloudfrontHeaders($request)
    {
        $headers = $request->headers;
        $headers->add(['x-forwarded-proto' => $headers->get('cloudfront-forwarded-proto')]);
    }
}
