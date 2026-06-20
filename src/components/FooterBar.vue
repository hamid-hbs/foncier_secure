<template>
  <footer class="border-t border-surface-100 bg-white mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 bg-gradient-to-br from-primary-600 to-accent-500 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-xs">FS</span>
          </div>
          <span class="text-sm font-semibold text-surface-700">FoncierSecure</span>
        </div>
        <p class="text-sm text-surface-400">&copy; {{ new Date().getFullYear() }} FoncierSecure Bénin. Tous droits réservés.</p>
        <div class="flex items-center gap-4 text-sm text-surface-400">
          <a href="#" class="hover:text-surface-600 transition-colors">Confidentialité</a>
          <a href="#" class="hover:text-surface-600 transition-colors">CGU</a>
          <a href="#" class="hover:text-surface-600 transition-colors">Aide</a>
        </div>
      </div>
    </div>
  </footer>
</template>
