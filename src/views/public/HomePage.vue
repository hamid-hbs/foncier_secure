<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const isAuth = computed(() => auth.isAuthenticated)

const modules = [
  { icon: 'fa-shield-halved', title: 'Vérifications', desc: 'Authentifiez vos titres fonciers avec notre système de vérification avancé.', link: '/parcelles' },
  { icon: 'fa-arrows-left-right', title: 'Transactions', desc: 'Sécurisez chaque étape de vos transactions immobilières.', link: '/parcelles' },
  { icon: 'fa-lock', title: 'Coffre-fort', desc: 'Stockez et organisez vos documents fonciers en toute sécurité.', link: '/auth/login' },
  { icon: 'fa-map-location-dot', title: 'Cartographie', desc: 'Visualisez les parcelles disponibles sur une carte interactive.', link: '/cartographie' },
]
</script>

<template>
  <div>
    <section class="relative py-24 overflow-hidden" style="background: var(--brand-dark);">
      <div class="absolute inset-0 opacity-10 pointer-events-none">
        <div class="absolute -top-20 -left-20 w-96 h-96 rounded-full" style="background: radial-gradient(circle, #40916c, transparent);"></div>
      </div>
      <div class="max-w-7xl mx-auto px-5 sm:px-8 text-center relative z-10">
        <h1 class="font-display font-extrabold text-4xl sm:text-6xl text-white mb-6">
          Sécurisez votre<br/>
          <span style="background: linear-gradient(135deg, #f2c558, #e8a020); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">patrimoine foncier</span>
        </h1>
        <p class="text-white/60 text-xl max-w-xl mx-auto mb-10">La plateforme de référence pour la sécurisation foncière au Bénin.</p>
        <div class="flex flex-wrap gap-4 justify-center">
          <router-link v-if="!isAuth" to="/auth/register" class="btn btn-gold btn-lg font-display font-bold shadow-gold">
            Commencer gratuitement <i class="fas fa-arrow-right ml-1"></i>
          </router-link>
          <router-link to="/parcelles" class="btn btn-outline btn-lg text-white border-white/20 hover:bg-white/10">
            Explorer les parcelles
          </router-link>
        </div>
      </div>
    </section>

    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-5 sm:px-8">
        <div class="text-center mb-12">
          <h2 class="font-display font-extrabold text-3xl text-stone-900">Tout ce qu'il vous faut</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <router-link v-for="m in modules" :key="m.title" :to="m.link" class="card-hover p-6">
            <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center text-brand mb-4">
              <i :class="['fas', m.icon, 'text-lg']"></i>
            </div>
            <h3 class="font-display font-bold text-stone-900 mb-2">{{ m.title }}</h3>
            <p class="text-sm text-stone-500 leading-relaxed">{{ m.desc }}</p>
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>
