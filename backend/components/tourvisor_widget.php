<?php $widget_lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'ru'; ?>
<section class="py-16 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-8 space-y-3">
            <h2 class="heading-font text-3xl md:text-4xl font-bold text-slate-900">
                <?php echo $widget_lang === 'ru' ? 'Найдите свой идеальный тур' : 'Find Your Perfect Tour'; ?>
            </h2>
            <p class="text-slate-600 max-w-2xl mx-auto">
                <?php echo $widget_lang === 'ru' 
                    ? 'Используйте поисковую форму для подбора лучших предложений по вашим параметрам' 
                    : 'Use the search form to find the best offers according to your parameters'; ?>
            </p>
        </div>
        <div class="max-w-7xl mx-auto bg-white rounded-3xl shadow-2xl p-8 md:p-10 border border-sky-100">
            <div class="tv-search-form tv-moduleid-9974456"></div>
        </div>
    </div>
</section>

