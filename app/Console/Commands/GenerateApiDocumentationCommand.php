<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route as RouteFacade;
use ReflectionClass;
use Throwable;

final class GenerateApiDocumentationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-api-documentation {--UI : Generate a UI index.html file for the API documentation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a JSON file in public/api-docs with details of all APIs grouped by name.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $routes = RouteFacade::getRoutes();
        $apiDetails = [
            'info' => [
                'name' => config('app.name'),
                'baseUrl' => config('app.url'),
                'version' => '1.0.0',
                'updated_at' => now()->toDateTimeString(),
            ],
            'endpoints' => [],
        ];

        foreach ($routes as $route) {
            /** @var Route $route */
            if (! $this->isApiRoute($route)) {
                continue;
            }

            $name = $route->getName() ?: $route->uri();
            $method = $route->methods()[0] ?? 'GET';

            $details = [
                'uri' => $route->uri(),
                'method' => $method,
                'authenticated' => in_array('auth:sanctum', $route->middleware()),
                'parameters' => $this->getRouteParameters($route),
                'responses' => $this->getRouteResponses($route),
            ];

            $this->setNestedValue($apiDetails['endpoints'], $name, $details);
        }

        $jsonContent = json_encode($apiDetails, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $directory = public_path('api-docs');

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filePath = $directory . '/data.json';
        File::put($filePath, $jsonContent);

        $this->info("API details have been saved to {$filePath}");

        if ($this->option('UI')) {
            $this->generateUi($directory);
            $this->info("API documentation UI has been generated at {$directory}/index.html");
        }

        return self::SUCCESS;
    }

    /**
     * Generate the UI for the API documentation.
     */
    private function generateUi(string $directory): void
    {
        $html = <<<'HTML_WRAP'
        <!DOCTYPE html>
        <html lang="en" class="h-full bg-white">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Nexora API Documentation</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <style>
                .method-get { @apply bg-blue-100 text-blue-800 border border-blue-200; }
                .method-post { @apply bg-green-100 text-green-800 border border-green-200; }
                .method-put { @apply bg-yellow-100 text-yellow-800 border border-yellow-200; }
                .method-patch { @apply bg-orange-100 text-orange-800 border border-orange-200; }
                .method-delete { @apply bg-red-100 text-red-800 border border-red-200; }

                .sidebar-item-active { @apply bg-indigo-50 text-indigo-700 border-l-indigo-600; }

                .group-collapsed svg { transform: rotate(-90deg); }
                .group-content-collapsed { display: none; }
                .toggle-icon { transition: transform 0.2s ease; }

                ::-webkit-scrollbar { width: 5px; }
                ::-webkit-scrollbar-track { @apply bg-transparent; }
                ::-webkit-scrollbar-thumb { @apply bg-gray-200 rounded-full; }
                ::-webkit-scrollbar-thumb:hover { @apply bg-gray-300; }

                html { scroll-behavior: smooth; }

                .endpoint-card { transition: all 0.3s ease; }
                .endpoint-card:target { @apply ring-2 ring-indigo-500 ring-offset-2; }
            </style>
        </head>
        <body class="h-full bg-gray-50 flex flex-col">
            <!-- Mobile header -->
            <div class="lg:hidden bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between sticky top-0 z-40">
                <div class="flex items-center space-x-3">
                    <div class="bg-indigo-600 p-1.5 rounded-lg">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="font-bold text-gray-900">Nexora <span class="text-indigo-600">API</span></span>
                </div>
                <button id="mobile-menu-button" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 flex overflow-hidden">
                <!-- Sidebar -->
                <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-gray-200 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-0 transition-transform duration-300 ease-in-out flex flex-col">
                    <div class="p-6 border-b border-gray-100 hidden lg:block">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="bg-indigo-600 p-2 rounded-xl shadow-lg shadow-indigo-200">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-lg font-bold text-gray-900 leading-none">Nexora</h1>
                                <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest mt-1">API Documentation</p>
                            </div>
                        </div>

                        <div class="relative">
                            <input type="text" id="api-search" placeholder="Search endpoints..." class="w-full pl-9 pr-4 py-2 bg-gray-50 border-gray-200 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 rounded-xl text-sm transition-all duration-200">
                            <div class="absolute left-3 top-2.5 text-gray-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-indigo-50 rounded-xl border border-indigo-100">
                            <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-1">Base URL</p>
                            <code id="base-url" class="text-xs font-mono font-bold text-indigo-700 break-all"></code>
                        </div>
                    </div>

                    <nav id="sidebar-nav" class="flex-1 overflow-y-auto p-4 space-y-8">
                        <div class="flex justify-center py-10">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        </div>
                    </nav>

                    <div class="p-4 border-t border-gray-100 text-[10px] text-gray-400 font-medium text-center">
                        <span id="last-updated"></span>
                    </div>
                </aside>

                <!-- Overlay for mobile sidebar -->
                <div id="sidebar-overlay" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 hidden lg:hidden"></div>

                <!-- Main Content -->
                <main class="flex-1 overflow-y-auto scroll-smooth" id="main-content">
                    <div id="api-content" class="max-w-5xl mx-auto px-4 sm:px-8 lg:px-12 py-12">
                        <div class="flex justify-center items-center py-20">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                        </div>
                    </div>
                </main>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', async () => {
                    const contentDiv = document.getElementById('api-content');
                    const sidebarNav = document.getElementById('sidebar-nav');
                    const lastUpdatedSpan = document.getElementById('last-updated');
                    const apiSearch = document.getElementById('api-search');
                    const sidebar = document.getElementById('sidebar');
                    const sidebarOverlay = document.getElementById('sidebar-overlay');
                    const mobileMenuButton = document.getElementById('mobile-menu-button');
                    const baseUrlCode = document.getElementById('base-url');

                    // Mobile menu toggle
                    function toggleMobileMenu() {
                        sidebar.classList.toggle('-translate-x-full');
                        sidebarOverlay.classList.toggle('hidden');
                        document.body.classList.toggle('overflow-hidden');
                    }

                    mobileMenuButton.addEventListener('click', toggleMobileMenu);
                    sidebarOverlay.addEventListener('click', toggleMobileMenu);

                    try {
                        const response = await fetch('data.json');
                        const data = await response.json();

                        window.apiData = data.endpoints;
                        window.apiInfo = data.info;

                        lastUpdatedSpan.textContent = `V${data.info.version} • ${new Date(data.info.updated_at).toLocaleDateString()}`;
                        baseUrlCode.textContent = data.info.baseUrl;

                        renderApp(data.endpoints);

                        apiSearch.addEventListener('input', (e) => {
                            const term = e.target.value.toLowerCase();
                            filterDocumentation(term);
                        });

                    } catch (error) {
                        console.error(error);
                        contentDiv.innerHTML = `<div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-xl">
                            <div class="flex">
                                <div class="ml-3">
                                    <p class="text-sm text-red-700 font-medium">Error loading API data. Please ensure data.json is present in the api-docs directory.</p>
                                </div>
                            </div>
                        </div>`;
                    }

                    function renderApp(data) {
                        renderSidebar(data);
                        renderDocumentation(data);
                    }

                    function renderSidebar(data) {
                        sidebarNav.innerHTML = '';

                        Object.keys(data).sort().forEach(group => {
                            const groupContainer = document.createElement('div');
                            groupContainer.className = 'space-y-2 group-wrapper';

                            const groupTitle = document.createElement('div');
                            groupTitle.className = 'flex items-center justify-between cursor-pointer px-3 mb-3 hover:text-indigo-600 transition-colors group-collapsed';
                            groupTitle.innerHTML = `
                                <h3 class="text-[11px] font-bold text-gray-400 uppercase tracking-[0.2em] group-hover:text-indigo-600 transition-colors">${group}</h3>
                                <svg class="h-3 w-3 text-gray-300 toggle-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            `;

                            const list = document.createElement('div');
                            list.className = 'space-y-0.5 group-content-collapsed';

                            groupTitle.addEventListener('click', () => {
                                groupTitle.classList.toggle('group-collapsed');
                                list.classList.toggle('group-content-collapsed');
                            });

                            groupContainer.appendChild(groupTitle);
                            renderSidebarEndpoints(data[group], list, group);

                            groupContainer.appendChild(list);
                            sidebarNav.appendChild(groupContainer);
                        });
                    }

                    function renderSidebarEndpoints(data, container, groupPath, level = 0) {
                        if (data.uri && data.method) {
                            const link = document.createElement('a');
                            const id = `endpoint-${data.method}-${data.uri.replace(/[\/\(\)\{\}]/g, '-')}`;
                            link.href = `#${id}`;
                            link.className = 'group flex items-center px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-indigo-600 rounded-xl transition-all duration-200 border-l-2 border-transparent';

                            if (level > 0) {
                                link.style.paddingLeft = `${(level * 12) + 12}px`;
                            }

                            const displayName = data.uri.split('/').pop().replace(/\{|\}/g, '');
                            const isResourceMethod = ['index', 'store', 'show', 'update', 'destroy'].includes(displayName);
                            const label = isResourceMethod ? `${displayName.charAt(0).toUpperCase() + displayName.slice(1)}` : displayName;

                            const inner = `<div class="flex items-center space-x-3 w-full">
                                <span class="text-[9px] font-bold w-10 text-center uppercase py-0.5 rounded-md border ${getMethodClass(data.method)}">${data.method}</span>
                                <span class="truncate font-medium">${label}</span>
                            </div>`;

                            link.innerHTML = inner;
                            container.appendChild(link);

                            link.addEventListener('click', () => {
                                document.querySelectorAll('#sidebar-nav a').forEach(a => a.classList.remove('sidebar-item-active'));
                                link.classList.add('sidebar-item-active');
                                if (window.innerWidth < 1024) {
                                    toggleMobileMenu();
                                }
                            });
                        } else {
                            Object.keys(data).sort().forEach(key => {
                                const subGroupContainer = document.createElement('div');
                                subGroupContainer.className = 'mt-2 first:mt-0';

                                const subTitle = document.createElement('div');
                                subTitle.className = 'text-[10px] font-bold text-gray-400 uppercase tracking-wider px-3 py-2 flex items-center justify-between cursor-pointer hover:text-indigo-600 transition-colors group-collapsed';
                                subTitle.style.paddingLeft = `${(level * 12) + 12}px`;
                                subTitle.innerHTML = `
                                    <div class="flex items-center space-x-2">
                                        <svg class="h-3 w-3 text-gray-300 toggle-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                        <span>${key}</span>
                                    </div>
                                `;

                                const subList = document.createElement('div');
                                subList.className = 'space-y-0.5 group-content-collapsed';

                                subTitle.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                    subTitle.classList.toggle('group-collapsed');
                                    subList.classList.toggle('group-content-collapsed');
                                });

                                subGroupContainer.appendChild(subTitle);
                                subGroupContainer.appendChild(subList);

                                container.appendChild(subGroupContainer);
                                renderSidebarEndpoints(data[key], subList, `${groupPath}.${key}`, level + 1);
                            });
                        }
                    }

                    function renderDocumentation(data) {
                        contentDiv.innerHTML = '';
                        Object.keys(data).sort().forEach(group => {
                            const groupSection = document.createElement('div');
                            groupSection.className = 'mb-20';

                            const groupHeader = document.createElement('div');
                            groupHeader.className = 'mb-10';
                            groupHeader.innerHTML = `
                                <h2 class="text-4xl font-black text-gray-900 tracking-tight capitalize mb-2">${group}</h2>
                                <div class="h-1.5 w-20 bg-indigo-600 rounded-full"></div>
                            `;
                            groupSection.appendChild(groupHeader);

                            const endpointsContainer = document.createElement('div');
                            endpointsContainer.className = 'space-y-16';
                            endpointsContainer.innerHTML = renderEndpoints(data[group]);

                            groupSection.appendChild(endpointsContainer);
                            contentDiv.appendChild(groupSection);
                        });
                    }

                    function renderEndpoints(data) {
                        let html = '';

                        if (data.uri && data.method) {
                            const id = `endpoint-${data.method}-${data.uri.replace(/[\/\(\)\{\}]/g, '-')}`;
                            const methodClass = getMethodClass(data.method);

                            html += `<div id="${id}" class="endpoint-card scroll-mt-24 group">
                                <div class="flex flex-wrap items-center gap-4 mb-6">
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase border-2 ${methodClass}">${data.method}</span>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-gray-400 font-mono text-lg">/</span>
                                        <code class="text-xl font-mono font-bold text-gray-900 tracking-tight">${data.uri}</code>
                                    </div>
                                    <div class="flex items-center ml-auto">
                                        ${data.authenticated ?
                                            '<span class="flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-green-50 text-green-700 border border-green-100"><svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>Authenticated</span>' :
                                            '<span class="flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-gray-50 text-gray-500 border border-gray-100"><svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 12.5a.5.5 0 01-.5-.5V7a.5.5 0 011 0v5a.5.5 0 01-.5.5z" clip-rule="evenodd"></path><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>Public</span>'
                                        }
                                    </div>
                                </div>

                                <div class="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl hover:shadow-indigo-500/5 transition-all duration-300">
                                    <div class="p-8 space-y-12">
                                        ${renderParameters(data.parameters)}
                                        ${renderResponses(data.responses)}
                                    </div>
                                </div>
                            </div>`;
                        } else {
                            Object.keys(data).sort().forEach(key => {
                                html += renderEndpoints(data[key]);
                            });
                        }

                        return html;
                    }

                    function getMethodClass(method) {
                        return `method-${method.toLowerCase()}`;
                    }

                    function renderParameters(params) {
                        if (!params || Object.keys(params).length === 0) {
                            return '<div class="flex items-center space-x-2 text-gray-400 bg-gray-50 p-4 rounded-2xl"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><p class="text-sm font-medium italic">No parameters required for this endpoint.</p></div>';
                        }

                        let html = '<div class="space-y-12">';

                        const sections = [
                            { key: 'url', title: 'URL Parameters', icon: 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1' },
                            { key: 'query', title: 'Query Parameters', icon: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' },
                            { key: 'body', title: 'Body Parameters', icon: 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4' }
                        ];

                        sections.forEach(section => {
                            if (params[section.key]) {
                                html += `<div>
                                    <div class="flex items-center space-x-2 mb-6">
                                        <div class="bg-gray-100 p-1.5 rounded-lg text-gray-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${section.icon}" /></svg>
                                        </div>
                                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">${section.title}</h4>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full">
                                            <thead>
                                                <tr class="text-left border-b border-gray-100">
                                                    <th class="pb-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider w-1/3">Parameter</th>
                                                    <th class="pb-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider w-1/4">Type</th>
                                                    <th class="pb-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Description</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-50">
                                                ${Object.entries(params[section.key]).map(([name, details]) => `
                                                    <tr class="group/row">
                                                        <td class="py-5 pr-4 align-top">
                                                            <div class="flex flex-col space-y-1">
                                                                <span class="text-sm font-mono font-bold text-gray-900 group-hover/row:text-indigo-600 transition-colors">${name}</span>
                                                                ${details.required ? '<span class="text-[9px] font-black text-red-500 uppercase tracking-tighter bg-red-50 self-start px-1.5 rounded">Required</span>' : '<span class="text-[9px] font-bold text-gray-300 uppercase tracking-tighter self-start">Optional</span>'}
                                                            </div>
                                                        </td>
                                                        <td class="py-5 pr-4 align-top">
                                                            <span class="px-2 py-0.5 rounded-md bg-gray-50 text-gray-500 text-[11px] font-medium border border-gray-100">
                                                                ${details.type || 'string'}
                                                            </span>
                                                        </td>
                                                        <td class="py-5 align-top">
                                                            <p class="text-sm text-gray-600 leading-relaxed">${details.description || '-'}</p>
                                                            ${details.options ? `<div class="mt-3 flex flex-wrap gap-1.5 items-center">
                                                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tight">Allowed:</span>
                                                                ${details.options.map(opt => `<code class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-md text-[10px] font-bold border border-indigo-100">${opt}</code>`).join('')}
                                                            </div>` : ''}
                                                        </td>
                                                    </tr>
                                                `).join('')}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>`;
                            }
                        });

                        html += '</div>';
                        return html;
                    }

                    function renderResponses(responses) {
                        if (!responses || Object.keys(responses).length === 0) {
                            return '';
                        }

                        let html = '<div>';
                        html += `
                            <div class="flex items-center space-x-2 mb-6">
                                <div class="bg-gray-100 p-1.5 rounded-lg text-gray-500">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                                </div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Expected Responses</h4>
                            </div>
                        `;

                        html += '<div class="space-y-8">';
                        Object.entries(responses).forEach(([code, details]) => {
                            const isSuccess = parseInt(code) >= 200 && parseInt(code) < 300;
                            const statusColor = isSuccess ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';

                            html += `
                                <div class="border border-gray-100 rounded-2xl p-6 bg-gray-50/30">
                                    <div class="flex items-center gap-3 mb-6">
                                        <span class="px-2 py-0.5 rounded-md ${statusColor} text-[11px] font-bold border border-current opacity-70">${code}</span>
                                        <span class="text-sm font-bold text-gray-700">${details.description}</span>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full">
                                            <thead>
                                                <tr class="text-left border-b border-gray-100">
                                                    <th class="pb-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider w-1/3">Field</th>
                                                    <th class="pb-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider w-1/4">Type</th>
                                                    <th class="pb-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Description</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-50">
                                                ${renderStructure(details.structure)}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div></div>';

                        return html;
                    }

                    function renderStructure(structure, prefix = '') {
                        let html = '';
                        Object.entries(structure).forEach(([key, details]) => {
                            const fieldName = prefix ? `${prefix}.${key}` : key;
                            html += `
                                <tr class="group/row">
                                    <td class="py-4 pr-4 align-top">
                                        <span class="text-sm font-mono font-bold text-gray-900 group-hover/row:text-indigo-600 transition-colors">${fieldName}</span>
                                    </td>
                                    <td class="py-4 pr-4 align-top">
                                        <span class="px-2 py-0.5 rounded-md bg-white text-gray-500 text-[11px] font-medium border border-gray-100">
                                            ${details.type || 'mixed'}
                                        </span>
                                    </td>
                                    <td class="py-4 align-top">
                                        <p class="text-sm text-gray-600 leading-relaxed">${details.description || '-'}</p>
                                    </td>
                                </tr>
                            `;

                            if (details.keys && Object.keys(details.keys).length > 0) {
                                html += renderStructure(details.keys, fieldName);
                            }
                        });
                        return html;
                    }

                    function filterDocumentation(term) {
                        if (!term) {
                            renderApp(window.apiData);
                            return;
                        }

                        const filtered = {};

                        Object.keys(window.apiData).forEach(group => {
                            const matchedEndpoints = findMatchingEndpoints(window.apiData[group], term);
                            if (Object.keys(matchedEndpoints).length > 0) {
                                filtered[group] = matchedEndpoints;
                            }
                        });

                        renderApp(filtered);
                    }

                    function findMatchingEndpoints(data, term) {
                        if (data.uri && data.method) {
                            const matches = data.uri.toLowerCase().includes(term) ||
                                          data.method.toLowerCase().includes(term);
                            return matches ? data : null;
                        }

                        const results = {};
                        Object.keys(data).forEach(key => {
                            const match = findMatchingEndpoints(data[key], term);
                            if (match) {
                                results[key] = match;
                            }
                        });

                        return Object.keys(results).length > 0 ? results : null;
                    }
                });
            </script>
        </body>
        </html>
        HTML_WRAP;

        File::put($directory . '/index.html', $html);
    }

    /**
     * Set a nested value in an array using a dot-notated key.
     *
     * @param  array<string, mixed>  $array
     */
    private function setNestedValue(array &$array, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $temp = &$array;

        foreach ($keys as $k) {
            if (! isset($temp[$k]) || ! is_array($temp[$k])) {
                $temp[$k] = [];
            }
            $temp = &$temp[$k];
        }

        $temp = $value;
    }

    /**
     * Determine if the given route is an API route.
     */
    private function isApiRoute(Route $route): bool
    {
        $uri = $route->uri();

        return str_starts_with($uri, 'api/') || in_array('api', $route->middleware(), true);
    }

    /**
     * Get the parameters for the given route.
     *
     * @return array<string, mixed>
     */
    private function getRouteParameters(Route $route): array
    {
        $parameters = [];

        // URL Parameters
        foreach ($route->parameterNames() as $name) {
            $parameters['url'][$name] = [
                'required' => true,
                'type' => 'string', // Default type
            ];
        }

        $action = $route->getAction();
        $controllerAction = $action['controller'] ?? null;
        $method = $route->methods()[0];

        if ($controllerAction) {
            try {
                if (str_contains((string) $controllerAction, '@')) {
                    [$class, $ctrlMethod] = explode('@', (string) $controllerAction);
                } else {
                    $class = $controllerAction;
                    $ctrlMethod = '__invoke';
                }

                if (class_exists($class)) {
                    $reflection = new ReflectionClass($class);
                    if ($reflection->hasMethod($ctrlMethod)) {
                        $methodReflection = $reflection->getMethod($ctrlMethod);
                        $docComment = $methodReflection->getDocComment();
                        if ($docComment) {
                            // 1. Extract @urlParam
                            preg_match_all('/@urlParam\s+(\w+)\s+(\w+)?\s*(.*)/', $docComment, $urlMatches, PREG_SET_ORDER);
                            foreach ($urlMatches as $match) {
                                $name = $match[1];
                                $type = $match[2] ?? 'string';
                                $description = $match[3] ?? '';

                                if (isset($parameters['url'][$name])) {
                                    $parameters['url'][$name] = array_merge($parameters['url'][$name], [
                                        'required' => str_contains($description, 'required'),
                                        'type' => $type,
                                        'description' => mb_trim(str_replace('required', '', $description)),
                                    ]);

                                    if (preg_match('/[Oo]ptions:\s*(.*)/', $description, $optionMatches)) {
                                        $options = array_map(trim(...), explode(',', $optionMatches[1]));
                                        $parameters['url'][$name]['options'] = $options;
                                    }
                                }
                            }

                            // 2. Extract @options for route parameters (legacy/simple support)
                            foreach (array_keys($parameters['url']) as $name) {
                                if (preg_match('/@options\s+(.*)/', $docComment, $matches)) {
                                    $options = array_map(trim(...), explode(',', $matches[1]));
                                    $parameters['url'][$name]['options'] = $options;
                                }
                            }
                        }

                        // Also check FormRequest for rules that might apply to URL parameters
                        foreach ($methodReflection->getParameters() as $parameter) {
                            $type = $parameter->getType();
                            if ($type && ! $type->isBuiltin()) {
                                $typeName = $type->getName();
                                if (is_subclass_of($typeName, \Illuminate\Foundation\Http\FormRequest::class)) {
                                    $requestInstance = new $typeName;
                                    if (method_exists($requestInstance, 'rules')) {
                                        $rules = $requestInstance->rules();
                                        $formattedRules = $this->formatRules($rules);
                                        foreach ($parameters['url'] as $name => $details) {
                                            if (isset($formattedRules[$name])) {
                                                $parameters['url'][$name] = array_merge($details, $formattedRules[$name]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (Throwable) {
                // Silence
            }
        }

        // Query parameters from DocBlock or FormRequest (for GET)
        if ($controllerAction) {
            $parameters['query'] = $this->getQueryParameters($controllerAction, $method === 'GET');
        }

        // Try to get validation rules for body if it's a POST/PUT/PATCH route
        if ($controllerAction && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $rules = $this->getValidationRules($controllerAction);
            if ($rules !== []) {
                $parameters['body'] = $this->formatRules($rules);
            }
        }

        return $parameters;
    }

    /**
     * Get query parameters from controller DocBlock or FormRequest.
     *
     * @return array<string, mixed>
     */
    private function getQueryParameters(string $controllerAction, bool $includeFormRequest = true): array
    {
        $parameters = [];

        try {
            if (str_contains($controllerAction, '@')) {
                [$class, $method] = explode('@', $controllerAction);
            } else {
                $class = $controllerAction;
                $method = '__invoke';
            }

            if (! class_exists($class)) {
                return [];
            }

            $reflection = new ReflectionClass($class);
            if (! $reflection->hasMethod($method)) {
                return [];
            }

            $methodReflection = $reflection->getMethod($method);

            // 1. Try to get from DocBlock
            $docComment = $methodReflection->getDocComment();
            if ($docComment) {
                preg_match_all('/@queryParam\s+(\w+)\s+(\w+)?\s*(.*)/', $docComment, $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    $name = $match[1];
                    $type = $match[2] ?? 'string';
                    $description = $match[3] ?? '';

                    $parameters[$name] = [
                        'required' => str_contains($description, 'required'),
                        'type' => $type,
                        'description' => mb_trim(str_replace('required', '', $description)),
                    ];

                    if (preg_match('/[Oo]ptions:\s*(.*)/', $description, $optionMatches)) {
                        $options = array_map(trim(...), explode(',', $optionMatches[1]));
                        $parameters[$name]['options'] = $options;
                    }
                }
            }

            // 2. Try to get from FormRequest if available and allowed
            if ($includeFormRequest) {
                foreach ($methodReflection->getParameters() as $parameter) {
                    $type = $parameter->getType();
                    if ($type && ! $type->isBuiltin()) {
                        $typeName = $type->getName();
                        if (is_subclass_of($typeName, \Illuminate\Foundation\Http\FormRequest::class)) {
                            $requestInstance = new $typeName;
                            if (method_exists($requestInstance, 'rules')) {
                                $rules = $requestInstance->rules();
                                $formattedRules = $this->formatRules($rules);
                                foreach ($formattedRules as $name => $details) {
                                    // If it's already in parameters from DocBlock, merge or override
                                    $parameters[$name] = isset($parameters[$name]) ? array_merge($parameters[$name], $details) : $details;
                                }
                            }
                        }
                    }
                }
            }
        } catch (Throwable) {
            // Silence errors
        }

        return $parameters;
    }

    /**
     * Format validation rules into a consistent structure.
     *
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function formatRules(array $rules): array
    {
        $formatted = [];

        foreach ($rules as $name => $ruleSet) {
            $ruleArray = is_string($ruleSet) ? explode('|', $ruleSet) : (array) $ruleSet;

            $details = [
                'required' => in_array('required', $ruleArray),
                'type' => 'string', // Default
            ];

            foreach ($ruleArray as $rule) {
                if (is_string($rule)) {
                    if ($rule === 'integer' || $rule === 'numeric') {
                        $details['type'] = 'integer';
                    } elseif ($rule === 'boolean') {
                        $details['type'] = 'boolean';
                    } elseif ($rule === 'array') {
                        $details['type'] = 'array';
                    } elseif ($rule === 'file' || $rule === 'image') {
                        $details['type'] = 'file';
                    } elseif (str_starts_with($rule, 'in:')) {
                        $details['options'] = explode(',', mb_substr($rule, 3));
                    }
                } elseif ($rule instanceof \Illuminate\Validation\Rules\Enum) {
                    $reflection = new ReflectionClass($rule);
                    if ($reflection->hasProperty('type')) {
                        $property = $reflection->getProperty('type');
                        $enumClass = $property->getValue($rule);
                        if (method_exists($enumClass, 'cases')) {
                            $details['options'] = array_map(fn ($case) => $case->value, $enumClass::cases());
                        }
                    }
                } elseif ($rule instanceof \Illuminate\Validation\Rules\In) {
                    $reflection = new ReflectionClass($rule);
                    if ($reflection->hasProperty('values')) {
                        $property = $reflection->getProperty('values');
                        $details['options'] = $property->getValue($rule);
                    }
                }
            }

            $formatted[$name] = $details;

            // Special handling for common fields to add descriptions if missing
            if ($name === 'per_page' && ! isset($details['description'])) {
                $formatted[$name]['description'] = 'Number of items per page';
            }
            if ($name === 'search' && ! isset($details['description'])) {
                $formatted[$name]['description'] = 'Search term';
            }
        }

        return $formatted;
    }

    /**
     * Try to extract validation rules from the controller action.
     *
     * @return array<string, mixed>
     */
    private function getValidationRules(string $controllerAction): array
    {
        try {
            if (str_contains($controllerAction, '@')) {
                [$class, $method] = explode('@', $controllerAction);
            } else {
                $class = $controllerAction;
                $method = '__invoke';
            }

            if (! class_exists($class)) {
                return [];
            }

            $reflection = new ReflectionClass($class);
            if (! $reflection->hasMethod($method)) {
                return [];
            }

            $parameters = $reflection->getMethod($method)->getParameters();

            foreach ($parameters as $parameter) {
                $type = $parameter->getType();
                if ($type && ! $type->isBuiltin()) {
                    $typeName = $type->getName();
                    if (is_subclass_of($typeName, \Illuminate\Foundation\Http\FormRequest::class)) {
                        $requestInstance = new $typeName;
                        if (method_exists($requestInstance, 'rules')) {
                            return $requestInstance->rules();
                        }
                    }
                }
            }
        } catch (Throwable) {
            // Silence errors
        }

        return [];
    }

    /**
     * Get the responses for the given route.
     *
     * @return array<int, mixed>
     */
    private function getRouteResponses(Route $route): array
    {
        $responses = [
            200 => [
                'description' => 'Success',
                'structure' => [
                    'success' => [
                        'type' => 'boolean',
                        'description' => 'Whether the request was successful',
                    ],
                    'message' => [
                        'type' => 'string',
                        'description' => 'Response message',
                    ],
                    'payload' => [
                        'type' => 'mixed',
                        'description' => 'Response data',
                        'keys' => [],
                    ],
                ],
            ],
            400 => [
                'description' => 'Bad Request',
                'structure' => [
                    'success' => [
                        'type' => 'boolean',
                        'description' => 'Always false',
                    ],
                    'message' => [
                        'type' => 'string',
                        'description' => 'Error message',
                    ],
                    'errors' => [
                        'type' => 'object',
                        'description' => 'Validation errors or other error details',
                    ],
                ],
            ],
            404 => [
                'description' => 'Not Found',
                'structure' => [
                    'success' => [
                        'type' => 'boolean',
                        'description' => 'Always false',
                    ],
                    'message' => [
                        'type' => 'string',
                        'description' => 'Error message',
                    ],
                    'errors' => [
                        'type' => 'object',
                        'description' => 'Error details',
                    ],
                ],
            ],
            422 => [
                'description' => 'Unprocessable Entity',
                'structure' => [
                    'success' => [
                        'type' => 'boolean',
                        'description' => 'Always false',
                    ],
                    'message' => [
                        'type' => 'string',
                        'description' => 'Validation error message',
                    ],
                    'errors' => [
                        'type' => 'object',
                        'description' => 'Validation errors by field',
                    ],
                ],
            ],
        ];

        $action = $route->getAction();
        $controllerAction = $action['controller'] ?? null;

        if ($controllerAction) {
            try {
                if (str_contains((string) $controllerAction, '@')) {
                    [$class, $ctrlMethod] = explode('@', (string) $controllerAction);
                } else {
                    $class = $controllerAction;
                    $ctrlMethod = '__invoke';
                }

                if (class_exists($class)) {
                    $reflection = new ReflectionClass($class);
                    if ($reflection->hasMethod($ctrlMethod)) {
                        $methodReflection = $reflection->getMethod($ctrlMethod);
                        $docComment = $methodReflection->getDocComment();

                        if ($docComment) {
                            // Extract @response [status_code] [description]
                            preg_match_all('/@response\s+(\d+)\s*(.*)/', $docComment, $respMatches, PREG_SET_ORDER);
                            foreach ($respMatches as $match) {
                                $code = (int) $match[1];
                                $description = $match[2] ?: 'Response';

                                if (! isset($responses[$code])) {
                                    $responses[$code] = [
                                        'description' => $description,
                                        'structure' => [],
                                    ];
                                } else {
                                    $responses[$code]['description'] = $description;
                                }
                            }

                            // Extract @responseKey [status_code]? [name] [type] [description]
                            preg_match_all('/@responseKey\s+(?:(\d+)\s+)?(\w+)\s+(\w+)?\s*(.*)/', $docComment, $matches, PREG_SET_ORDER);
                            foreach ($matches as $match) {
                                $code = $match[1] !== '' && $match[1] !== '0' ? (int) $match[1] : 200;
                                $name = $match[2];
                                $type = $match[3] ?? 'string';
                                $description = $match[4] ?? '';

                                if (! isset($responses[$code])) {
                                    $responses[$code] = [
                                        'description' => $code === 200 ? 'Success' : 'Response',
                                        'structure' => [],
                                    ];
                                }

                                if ($code === 200 && isset($responses[200]['structure']['payload'])) {
                                    $responses[200]['structure']['payload']['keys'][$name] = [
                                        'type' => $type,
                                        'description' => $description,
                                    ];
                                } elseif (in_array($code, [400, 404, 422]) && isset($responses[$code]['structure']['errors'])) {
                                    // Special handling for standard error responses if we want to nest under 'errors'
                                    if (! isset($responses[$code]['structure']['errors']['keys'])) {
                                        $responses[$code]['structure']['errors']['keys'] = [];
                                    }
                                    $responses[$code]['structure']['errors']['keys'][$name] = [
                                        'type' => $type,
                                        'description' => $description,
                                    ];
                                } else {
                                    $responses[$code]['structure'][$name] = [
                                        'type' => $type,
                                        'description' => $description,
                                    ];
                                }
                            }
                        }
                    }
                }
            } catch (Throwable) {
                // Silence
            }
        }

        return $responses;
    }
}
