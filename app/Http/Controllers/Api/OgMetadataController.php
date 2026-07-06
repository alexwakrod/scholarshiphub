<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use DOMDocument;

class OgMetadataController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['url' => 'required|url']);

        $url = $request->input('url');

        try {
            $response = Http::timeout(5)->get($url);
            if (!$response->successful()) {
                return response()->json(['error' => 'Unable to fetch URL.'], 422);
            }

            $html = $response->body();
            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $metaTags = $doc->getElementsByTagName('meta');

            $data = [
                'title' => '',
                'description' => '',
                'image' => null,
            ];

            // Extract <title>
            $titles = $doc->getElementsByTagName('title');
            if ($titles->length > 0) {
                $data['title'] = $titles->item(0)->textContent;
            }

            foreach ($metaTags as $tag) {
                $property = $tag->getAttribute('property') ?: $tag->getAttribute('name');
                $content = $tag->getAttribute('content');
                if (!$property || !$content) continue;

                switch (strtolower($property)) {
                    case 'og:title':
                        $data['title'] = $content;
                        break;
                    case 'og:description':
                        $data['description'] = $content;
                        break;
                    case 'og:image':
                        $data['image'] = $content;
                        break;
                }
            }

            return response()->json($data);
        } catch (\Throwable $e) {
            Log::error('OgMetadataController error: ' . $e->getMessage(), ['url' => $url]);
            return response()->json(['error' => 'Failed to extract metadata.'], 500);
        }
    }
}