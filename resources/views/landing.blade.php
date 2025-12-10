<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Niedlich! - Home</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-300 text-gray-900">
    <header class="bg-white/80 backdrop-blur border-b border-gray-200">
        <nav class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">

            <!-- Left side of the Navbar -->
            <div class="flex items-center space-x-6">
                <!-- Logo -->
                <a href="/" class="text-xl font-bold tracking-tight text-amber-700">
                    Niedlich!
                </a>

                <!-- Nav Links -->
                <div class="hidden md:flex items-center space-x-4 text-sm font-medium text-gray-600">
                    <a href="{{ route('quickstart') }}" class="hover:text-gray-900 transition">Quickstart</a>
                    <a href="#" class="coming-soon">Gallery</a>
                    <a href="{{ route('docs_api') }}" class="hover:text-gray-900 transition">API Docs</a>
                </div>
            </div>

            <!-- Right side of the Navbar -->
            <div class="flex items-center space-x-4 text-sm font-medium">
                <a href="https://github.com/StrangeAntiquarkLab/niedlich.org" target="_blank"
                class="hidden md:flex items-center space-x-1 text-gray-600 hover:text-gray-900 transition">
                    <span>Follow on GitHub</span>
                    <i data-lucide="github" class="w-5 h-5"></i>
                </a>

                <a href="{{ route('login') }}"
                class="px-3 py-1.5 bg-amber-600 text-white rounded-md text-sm hover:bg-amber-700 transition">
                    Login
                </a>
            </div>

        </nav>
    </header>

    <!-- Main Panel -->
    <main class="max-w-7xl mx-auto py-20 px-4 text-center">
        <!-- TODO: Add a cute background here! -->

        <h2 class="text-5xl font-bold mb-6">Welcome to Niedlich!</h2>
        <p class="text-xl mb-8">
            Your new, favorite
            <x-tooltip text="Cuteness as a Service" placement="top" underline="true">
                CaaS
            </x-tooltip>
            provider!
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row justify-center gap-4 mt-8">
            <!-- Get Started Btn -->
            <a href="{{ route('quickstart') }}"
            class="bg-amber-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-amber-700 transition">
                Get Started
            </a>

            <!-- Give me Cuteness Btn -->
            <a href="{{ url('/caas/cat'); }}"
            class="bg-pink-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-pink-600 transition">
                Give Me Cuteness
            </a>
        </div>
    </main>

    <section class="bg-gray-100 py-20">
        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-10 px-4 items-stretch">

            <!-- Col 1: Niedlich -->
            <x-vintage-card>
                <h3 class="text-2xl font-bold font-sans mb-1 text-gray-800 tracking-tight">
                    What is "Niedlich"?
                </h3>
                <p class="mb-3 font-sans text-gray-700">
                    The German word for "cute", or as an old dictionary described it:
                </p>

                <h4 class="text-xl font-bold mb-3 pt-1 text-gray-800 uppercase tracking-wide">
                    NIEDLICH <span class="text-gray-500 text-base font-normal">(Adj., German)</span>
                </h4>

                <div class="border-t border-amber-300 mb-3 opacity-60"></div>

                <p class="mb-2"><span class="font-semibold">Pronunciation:</span> /ˈniːtlɪç/</p>

                <p class="mb-2"><span class="font-semibold">Def. 1.</span> Endued with a smallness or pleasing charm; that which doth delight the eye and engender affection in the beholder.</p>
                <p class="mb-3"><span class="font-semibold">Def. 2.</span> Of a nature gentle, innocent, and daintily attractive; suited to evoke tenderness or regard.</p>

                <p class="mb-2"><span class="font-semibold">Etymology.</span> From the Middle High German <em>nidelic</em>, akin to <em>nid</em> (little, small), with the diminutive suffix <em>-lich</em>.</p>

                <p class="mb-2"><span class="font-semibold">Synonyms.</span> lieblich, reizend, putzig, entzückend.</p>
                <p class="mb-3"><span class="font-semibold">Antonyms.</span> häßlich, abstoßend, unattraktiv.</p>

                <p class="text-sm text-gray-600 italic mt-auto">Note. The term doth carry a sentiment of heart’s favour, appealing rather to perception of warmth than measure of exactitude.</p>
            </x-vintage-card>

            <!-- Col 2: CaaS -->
            <x-vintage-card>
                <h3 class="text-2xl font-bold font-sans mb-1 text-gray-800 tracking-tight">
                    What is "CaaS"?
                </h3>
                <p class="mb-3 font-sans text-gray-700">
                    Cuteness as a Service! Or as an old lexicon <em>would have</em> described it:
                </p>

                <h4 class="text-xl font-bold mb-3 pt-1 text-gray-800 tracking-wide">
                    C. a. a. S. <span class="text-gray-500 text-base font-normal">(Abbrv., Concept)</span>
                </h4>

                <div class="border-t border-amber-300 mb-3 opacity-60"></div>

                <p class="mb-2"><span class="font-semibold">Pronunciation:</span> /ˈsiː ˌeɪ ˌeɪ ˈɛs/</p>

                <p class="mb-2"><span class="font-semibold">Description.</span> Denotes a systematic arrangement, chiefly of modern mechanical or computational design, by which tokens of charm and gentle amusement are issued or made available upon request, serving the purpose of delighting the observer.</p>

                <p class="mb-2"><span class="font-semibold">Usage.</span> Employed in contemporary discourse to signify a structured provision of endearing content, framed in the manner of technical services.</p>

                <p class="text-sm text-gray-600 italic mt-auto">Note. A term signifying an organised bestowal of delight, amusingly fashioned after the parlance of modern computing.</p>
            </x-vintage-card>

        </div>
    </section>


    <footer class="bg-white py-6 text-center text-gray-600">
        &copy; {{ date('Y') }} Meine App. Alle Rechte vorbehalten.
    </footer>
</body>
</html>
