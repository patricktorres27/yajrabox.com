<?php

namespace App\Http\Controllers;

use App\Documentation;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class DocsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  Documentation  $docs
     * @return void
     */
    public function __construct(
        protected Documentation $docs
    ) {
    }

    /**
     * Show the root documentation page (/docs).
     *
     * @param  string|null  $package
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function showRootPage(string $package = null)
    {
        $package = $package ?: DEFAULT_PACKAGE;

        return redirect("docs/$package/".DEFAULT_VERSION);
    }

    /**
     * Show a documentation page.
     *
     * @param  string  $package
     * @param  string  $version
     * @param  string|null  $folder
     * @param  string|null  $page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function show(string $package, string $version, string $folder = null, string $page = null)
    {
        if (! $this->isVersion($package, $version)) {
            return redirect("docs/$package/".DEFAULT_VERSION.'/'.$version, 301);
        }

        if (! defined('CURRENT_VERSION')) {
            define('CURRENT_VERSION', $version);
        }

        $sectionPage = $folder ?: 'installation';
        if (! is_null($page)) {
            $sectionPage .= '/'.$page;
        }

        $content = $this->docs->get($package, $version, $sectionPage);

        $title = (new Crawler($content))->filterXPath('//h1');

        $section = '';
        if ($this->docs->pageExists($package, $version, $sectionPage)) {
            $section .= '/'.$sectionPage;
        } elseif (! is_null($folder)) {
            return redirect("/docs/$package/{$version}");
        }

        $canonical = null;

        if ($this->docs->pageExists($package, DEFAULT_VERSION, $sectionPage)) {
            $canonical = "docs/$package/".DEFAULT_VERSION.'/'.$sectionPage;
        }

        return view('docs.show', [
            'title' => count($title) ? $title->text() : null,
            'index' => $this->docs->getIndex($package, $version),
            'package' => $package,
            'content' => $content,
            'currentVersion' => $version,
            'versions' => Documentation::getDocVersions($package),
            'currentSection' => $section,
            'canonical' => $canonical,
        ]);
    }

    /**
     * Determine if the given URL segment is a valid version.
     *
     * @param  string  $package
     * @param  string  $version
     * @return bool
     */
    protected function isVersion(string $package, string $version): bool
    {
        return in_array($version, array_keys(Documentation::getDocVersions($package)));
    }

    /**
     * Show the documentation index JSON representation.
     *
     * @param  string  $package
     * @param  string  $version
     * @param  \App\Documentation  $docs
     * @return array|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function index(string $package, string $version, Documentation $docs)
    {
        $major = Str::before($version, '.');
        $versions = Documentation::getDocVersions($package);

        if (Str::before(array_values($versions)[1], '.') + 1 === (int) $major) {
            $version = $major = 'master';
        }

        if (! $this->isVersion($package, $version)) {
            return redirect("docs/$package/".DEFAULT_VERSION.'/index.json', 301);
        }

        if ($major !== 'master' && $major < 9) {
            return [];
        }

        return response()->json($docs->indexArray($package, $version));
    }
}
