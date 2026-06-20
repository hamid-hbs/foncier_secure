<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const isAuth = computed(() => auth.isAuthenticated)

const modules = [
  { icon: 'fa-shield-alt', title: 'Vérifications', desc: 'Authentifiez vos titres fonciers avec notre système de vérification avancé.', link: '/verifications' },
  { icon: 'fa-arrows-left-right', title: 'Transactions', desc: 'Sécurisez chaque étape de vos transactions immobilières.', link: '/transactions' },
  { icon: 'fa-folder', title: 'Coffre numérique', desc: 'Stockez et partagez vos documents en toute sécurité.', link: '/coffre' },
]

const stats = [
  { value: '15 000+', label: 'Terrains vérifiés', icon: 'fa-circle-check' },
  { value: '8 500+', label: 'Transactions sécurisées', icon: 'fa-arrows-left-right' },
  { value: '98%', label: 'Conflits résolus', icon: 'fa-chart-line' },
  { value: '120+', label: 'Professionnels partenaires', icon: 'fa-users' },
]
</script>

<template>
  <div>
    <section class="relative overflow-hidden" style="background: linear-gradient(135deg, #1B4332, #2D6A4F, #1B4332);">
      <div class="absolute inset-0 opacity-30" style="background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iMiIvPjwvZz48L2c+PC9zdmc+');"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
        <div class="max-w-3xl">
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm mb-6" style="background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.8);">
            <span class="w-2 h-2 rounded-full animate-pulse" style="background: #52B788;"></span>
            Plateforme de sécurisation foncière #1 au Bénin
          </div>
          <h1 class="text-4xl lg:text-6xl font-extrabold text-white leading-tight mb-6">
            Sécurisez vos biens fonciers<br />
            <span style="background: linear-gradient(90deg, #95D5B2, #52B788); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">en toute confiance</span>
          </h1>
          <p class="text-lg mb-10 leading-relaxed max-w-xl" style="color: rgba(255,255,255,0.7);">
            Vérification, transaction, stockage et médiation : la plateforme tout-en-un pour protéger votre patrimoine immobilier.
          </p>
          <div class="flex flex-wrap gap-4">
            <router-link v-if="!isAuth" to="/auth/register" class="inline-flex items-center gap-2 font-semibold py-3.5 px-8 transition-all active:scale-[0.98]" style="background: white; color: #1B4332; border-radius: 12px;">
              Commencer gratuitement
              <i class="fas fa-arrow-right"></i>
            </router-link>
            <router-link v-if="isAuth" to="/mon-profil" class="inline-flex items-center gap-2 font-semibold py-3.5 px-8 transition-all" style="background: white; color: #1B4332; border-radius: 12px;">
              Mon tableau de bord
              <i class="fas fa-arrow-right"></i>
            </router-link>
            <router-link to="/observatoire" class="inline-flex items-center gap-2 font-semibold py-3.5 px-8 transition-all" style="border: 2px solid rgba(255,255,255,0.2); color: white; border-radius: 12px;">
              Voir l'observatoire
            </router-link>
          </div>
        </div>
      </div>
      <div class="absolute bottom-0 left-0 right-0 h-32" style="background: linear-gradient(to top, var(--bg-page), transparent);"></div>
    </section>

    <section class="py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
          <h2 class="text-3xl font-bold" style="color: var(--text-primary);">Plateforme complète</h2>
          <p class="mt-2 max-w-lg mx-auto" style="color: var(--text-secondary);">Tous les outils pour une gestion foncière sereine et transparente</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <router-link v-for="m in modules" :key="m.title" :to="m.link" class="card p-7" style="cursor: pointer;">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-4" style="background: var(--bg-page);">
              <i :class="['fas', m.icon]" style="color: var(--green-tree); font-size: 1.25rem;"></i>
            </div>
            <h3 class="text-lg font-semibold" style="color: var(--text-primary);">{{ m.title }}</h3>
            <p class="text-sm mt-1.5 leading-relaxed" style="color: var(--text-secondary);">{{ m.desc }}</p>
          </router-link>
        </div>
      </div>
    </section>

    <section class="py-16" style="background: var(--green-tree);">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="s in stats" :key="s.label" class="text-center">
            <div class="flex justify-center mb-3">
              <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background: rgba(255,255,255,0.1);">
                <i :class="['fas', s.icon]" style="color: white; font-size: 1.25rem;"></i>
              </div>
            </div>
            <div class="text-3xl font-bold text-white mb-1">{{ s.value }}</div>
            <div class="text-sm" style="color: rgba(255,255,255,0.7);">{{ s.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="card text-center p-12 max-w-2xl mx-auto">
          <h2 class="text-3xl font-bold mb-3" style="color: var(--text-primary);">Prêt à sécuriser vos biens ?</h2>
          <p class="mb-8" style="color: var(--text-secondary);">Rejoignez des milliers de Béninois qui font déjà confiance à FoncierSecure.</p>
          <router-link v-if="!isAuth" to="/auth/register" class="btn-gold inline-flex items-center gap-2 py-3.5 px-10 text-lg">
            Créer un compte gratuit <i class="fas fa-arrow-right"></i>
          </router-link>
          <router-link v-if="isAuth" to="/mon-profil" class="btn-gold inline-flex items-center gap-2 py-3.5 px-10 text-lg">
            Mon tableau de bord <i class="fas fa-arrow-right"></i>
          </router-link>
        </div>
      </div>
    </section>

    <footer class="py-8" style="border-top: 1px solid var(--border);">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm" style="color: var(--text-secondary);">
        &copy; {{ new Date().getFullYear() }} FoncierSecure Bénin. Tous droits réservés.
      </div>
    </footer>
  </div>
</template>
