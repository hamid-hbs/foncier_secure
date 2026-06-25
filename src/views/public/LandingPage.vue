<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import parcelleApi from '@/api/parcelle'
import professionnelApi from '@/api/professionnel'
import blockchainApi from '@/api/blockchain'
import heroLand from '@/assets/hero-land.jpg'
import cadastreImg from '@/assets/cadastre.jpg'

const auth = useAuthStore()
const isAuth = computed(() => auth.isAuthenticated)
const stats = ref({ parcelles: 0, professionnels: 0, blockchain_valide: false })
const recentParcelles = ref([])
const featuredPros = ref([])

onMounted(async () => {
  try {
    const [pRes, prRes, bRes] = await Promise.allSettled([
      parcelleApi.listPublic({ per_page: 1 }),
      professionnelApi.list({ per_page: 1 }),
      blockchainApi.verify(),
    ])
    if (pRes.status === 'fulfilled') {
      stats.value.parcelles = pRes.value.data?.meta?.total || 0
      const data = pRes.value.data?.data || pRes.value.data || []
      recentParcelles.value = data.slice(0, 3)
    }
    if (prRes.status === 'fulfilled') {
      stats.value.professionnels = prRes.value.data?.meta?.total || 0
      const data = prRes.value.data?.data || prRes.value.data || []
      featuredPros.value = data.slice(0, 3)
    }
    if (bRes.status === 'fulfilled') {
      stats.value.blockchain_valide = bRes.data?.valide || false
    }
  } catch (e) { /* silent */ }
})

const features = [
  {
    icon: 'fa-shield-halved',
    title: 'Vérification foncière',
    desc: 'Authentifiez vos titres avec notre système de vérification avancé adossé à la blockchain.',
    accent: 'bg-brand/10 text-brand',
  },
  {
    icon: 'fa-file-signature',
    title: 'Transactions sécurisées',
    desc: 'Sécurisez chaque étape de vos transactions immobilières avec l\'accompagnement d\'un notaire.',
    accent: 'bg-gold/10 text-gold-dark',
  },
  {
    icon: 'fa-lock',
    title: 'Coffre numérique',
    desc: 'Stockez, organisez et partagez vos documents fonciers en toute confidentialité.',
    accent: 'bg-success/10 text-success',
  },
  {
    icon: 'fa-map-location-dot',
    title: 'Cartographie interactive',
    desc: 'Visualisez les parcelles disponibles sur une carte géographique en temps réel.',
    accent: 'bg-info/10 text-info',
  },
  {
    icon: 'fa-users',
    title: 'Réseau de professionnels',
    desc: 'Accédez à un réseau certifié de notaires et géomètres pour accompagner vos projets.',
    accent: 'bg-brand/10 text-brand',
  },
  {
    icon: 'fa-link',
    title: 'Traçabilité blockchain',
    desc: 'Chaque action est enregistrée de manière immuable pour garantir l\'intégrité de vos données.',
    accent: 'bg-gold/10 text-gold-dark',
  },
]

const steps = [
  { num: '01', title: 'Créez votre compte', desc: 'Inscription gratuite en quelques secondes, vérification d\'identité sécurisée.' },
  { num: '02', title: 'Déclarez vos parcelles', desc: 'Enregistrez vos biens fonciers avec photos, documents et coordonnées GPS.' },
  { num: '03', title: 'Vérifiez & sécurisez', desc: 'Nos géomètres certifiés authentifient vos titres et génèrent un rapport officiel.' },
  { num: '04', title: 'Transactez en toute confiance', desc: 'Réalisez vos achats et ventes avec l\'accompagnement d\'un notaire partenaire.' },
]

</script>

<template>
  <div class="overflow-x-hidden">

    <!-- ===== HERO ===== -->
    <section class="relative min-h-screen flex items-center overflow-hidden"
      style="background: linear-gradient(160deg, var(--brand-dark) 0%, #0f3324 50%, #0a281c 100%);">
      <!-- Organic blobs -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-[700px] h-[700px] rounded-full opacity-10"
          style="background: radial-gradient(circle, #6bb99e 0%, transparent 70%);"></div>
        <div class="absolute -bottom-48 -left-48 w-[600px] h-[600px] rounded-full opacity-10"
          style="background: radial-gradient(circle, #e8a020 0%, transparent 70%);"></div>
        <div class="absolute top-1/3 left-1/2 -translate-x-1/2 w-[500px] h-[500px] rounded-full opacity-[0.04]"
          style="background: radial-gradient(circle, #ffffff 0%, transparent 70%);"></div>
      </div>
      <!-- Subtle pattern overlay -->
      <div class="absolute inset-0 opacity-[0.02]"
        style="background-image: repeating-linear-gradient(45deg, transparent, transparent 40px, rgba(255,255,255,0.03) 40px, rgba(255,255,255,0.03) 41px);"></div>

      <div class="relative max-w-7xl mx-auto px-5 sm:px-8 py-32 lg:py-36 w-full">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16">
          <!-- Left: Text -->
          <div class="max-w-xl animate-fade-in">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/8 border border-white/10 text-white/70 text-xs font-semibold tracking-wide mb-6 backdrop-blur-sm">
              <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
              Plateforme de sécurisation foncière
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-display font-extrabold text-white leading-[1.1] mb-5">
              Simplifiez la gestion<br/>
              <span class="text-gold-light">de votre patrimoine foncier</span>
            </h1>

            <p class="text-base sm:text-lg text-white/50 leading-relaxed mb-8 max-w-lg">
              Vérifiez, transactionnez et sécurisez vos biens fonciers en toute confiance grâce à notre plateforme digitale agréée par l'État.
            </p>

            <!-- CTAs -->
            <div class="flex flex-wrap gap-3">
              <router-link
                v-if="!isAuth"
                to="/auth/register"
                class="inline-flex items-center gap-2 font-display font-bold text-brand-dark py-3.5 px-7 rounded-xl transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] shadow-lg"
                style="background: linear-gradient(135deg, #f2c558, #e8a020);"
              >
                Créer un compte gratuit
                <i class="fas fa-arrow-right text-sm"></i>
              </router-link>
              <router-link
                v-else
                :to="auth.userRole === 'citoyen' ? '/tableau-de-bord' : auth.userRole === 'notaire' ? '/notaire/dashboard' : auth.userRole === 'geometre' ? '/geometre/dashboard' : '/admin/dashboard'"
                class="inline-flex items-center gap-2 font-display font-bold text-brand-dark py-3.5 px-7 rounded-xl shadow-lg"
                style="background: linear-gradient(135deg, #f2c558, #e8a020);"
              >
                Mon espace
                <i class="fas fa-arrow-right text-sm"></i>
              </router-link>
              <router-link
                to="/parcelles"
                class="inline-flex items-center gap-2 font-semibold text-white/70 hover:text-white border border-white/20 hover:border-white/40 py-3.5 px-7 rounded-xl transition-all duration-200 backdrop-blur-sm hover:bg-white/5"
              >
                <i class="fas fa-map text-sm"></i>
                Explorer les parcelles
              </router-link>
            </div>

            <!-- Trust badges -->
            <div class="flex flex-wrap items-center gap-6 mt-10 pt-8 border-t border-white/10">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-green-400/20 to-green-600/10 flex items-center justify-center ring-1 ring-green-400/20">
                  <i class="fas fa-map text-green-400 text-sm"></i>
                </div>
                <span class="text-xs text-white/50 font-medium leading-snug">Parcelles<br/><span class="text-white/80">{{ stats.parcelles }} référencées</span></span>
              </div>
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-yellow-400/20 to-yellow-600/10 flex items-center justify-center ring-1 ring-yellow-400/20">
                  <i class="fas fa-users text-yellow-400 text-sm"></i>
                </div>
                <span class="text-xs text-white/50 font-medium leading-snug">Experts<br/><span class="text-white/80">{{ stats.professionnels }} professionnels</span></span>
              </div>
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-600/20 to-amber-800/10 flex items-center justify-center ring-1 ring-amber-600/20">
                  <i class="fas fa-link text-amber-700 text-sm"></i>
                </div>
                <span class="text-xs text-white/50 font-medium leading-snug">Blockchain<br/><span class="text-white/80">{{ stats.blockchain_valide ? 'Intègre' : '—' }}</span></span>
              </div>
            </div>
          </div>

          <!-- Right: Images -->
          <div class="hidden lg:block relative w-full self-stretch">
            <!-- Main image -->
            <div class="absolute inset-0 overflow-hidden rounded-2xl shadow-2xl shadow-ink/20">
              <img
                :src="heroLand"
                alt="Vue aérienne de parcelles agricoles au Bénin"
                class="h-full w-full object-cover"
              />
              <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-stone-900/70 to-transparent p-6 text-white">
                <div class="font-mono text-[10px] uppercase tracking-[0.2em] opacity-70">N° 06°22'N · 02°26'E</div>
                <div class="mt-1 font-display text-lg"></div>
              </div>
            </div>
            <!-- Small overlay card -->
            <div class="absolute -bottom-5 left-6 w-44 rotate-[-3deg] rounded-xl bg-white p-3 shadow-xl shadow-stone-900/15 ring-1 ring-stone-200">
              <img
                :src="cadastreImg"
                alt="Extrait cadastral"
                class="aspect-square w-full object-cover rounded-lg"
              />
              <div class="mt-2 font-mono text-[9px] uppercase tracking-widest text-stone-400">Cadastre</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== POURQUOI FONCIERSECURE ===== -->
    <section class="py-20" style="background: var(--bg);">
      <div class="max-w-7xl mx-auto px-5 sm:px-8">
        <div class="text-center max-w-2xl mx-auto mb-14">
          <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest text-brand bg-brand-50 mb-4">Nos atouts</span>
          <h2 class="font-display font-extrabold text-3xl sm:text-4xl text-stone-900 mb-4">Pourquoi choisir FoncierSecure ?</h2>
          <p class="text-stone-500 text-lg">Une plateforme conçue pour restaurer la confiance dans le foncier au Bénin.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="card border-0 text-center p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 bg-white rounded-2xl border border-stone-100">
            <div class="w-14 h-14 rounded-2xl bg-brand-50 flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-link text-brand text-xl"></i>
            </div>
            <h3 class="font-display font-bold text-stone-900 mb-2">Certification blockchain</h3>
            <p class="text-sm text-stone-500 leading-relaxed">Chaque titre foncier est horodaté et certifié sur la blockchain pour une traçabilité immuable.</p>
          </div>
          <div class="card border-0 text-center p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 bg-white rounded-2xl border border-stone-100">
            <div class="w-14 h-14 rounded-2xl bg-brand-50 flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-users text-brand text-xl"></i>
            </div>
            <h3 class="font-display font-bold text-stone-900 mb-2">Réseau d'experts certifiés</h3>
            <p class="text-sm text-stone-500 leading-relaxed">Notaires et géomètres partenaires vous accompagnent à chaque étape de vos démarches.</p>
          </div>
          <div class="card border-0 text-center p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 bg-white rounded-2xl border border-stone-100">
            <div class="w-14 h-14 rounded-2xl bg-brand-50 flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-lock text-brand text-xl"></i>
            </div>
            <h3 class="font-display font-bold text-stone-900 mb-2">Confidentialité totale</h3>
            <p class="text-sm text-stone-500 leading-relaxed">Vos documents et données personnelles sont chiffrés et protégés selon les normes les plus strictes.</p>
          </div>
          <div class="card border-0 text-center p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 bg-white rounded-2xl border border-stone-100">
            <div class="w-14 h-14 rounded-2xl bg-brand-50 flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-gauge-high text-brand text-xl"></i>
            </div>
            <h3 class="font-display font-bold text-stone-900 mb-2">Simplicité d'utilisation</h3>
            <p class="text-sm text-stone-500 leading-relaxed">Une plateforme intuitive accessible à tous, conçue avec et pour les citoyens béninois.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== CONNECTED USER ===== -->
    <section v-if="isAuth" class="py-12 bg-white border-b border-stone-100">
      <div class="max-w-7xl mx-auto px-5 sm:px-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
          <div>
            <h2 class="font-display font-bold text-xl text-stone-900">
              Bonjour, {{ auth.user?.prenom }} 👋
            </h2>
            <p class="text-sm text-stone-500">Accédez rapidement à vos outils</p>
          </div>
          <router-link :to="auth.userRole === 'citoyen' ? '/tableau-de-bord' : auth.userRole === 'notaire' ? '/notaire/dashboard' : auth.userRole === 'geometre' ? '/geometre/dashboard' : '/admin/dashboard'" class="btn btn-ghost btn-sm">
            Tableau de bord <i class="fas fa-arrow-right text-xs ml-1"></i>
          </router-link>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <router-link to="/mon-profil" class="flex items-center gap-3 p-4 rounded-xl border border-stone-200 hover:border-brand-200 hover:bg-brand-50/30 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand group-hover:scale-110 transition-transform">
              <i class="fas fa-user"></i>
            </div>
            <div>
              <p class="text-sm font-semibold text-stone-900">Mon profil</p>
              <p class="text-xs text-stone-400">Gérer mon compte</p>
            </div>
          </router-link>
          <router-link v-if="auth.userRole === 'citoyen'" to="/citoyen/parcelles" class="flex items-center gap-3 p-4 rounded-xl border border-stone-200 hover:border-brand-200 hover:bg-brand-50/30 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand group-hover:scale-110 transition-transform">
              <i class="fas fa-map"></i>
            </div>
            <div>
              <p class="text-sm font-semibold text-stone-900">Mes parcelles</p>
              <p class="text-xs text-stone-400">Voir mes biens</p>
            </div>
          </router-link>
          <router-link v-if="auth.userRole === 'citoyen'" to="/citoyen/verifications" class="flex items-center gap-3 p-4 rounded-xl border border-stone-200 hover:border-brand-200 hover:bg-brand-50/30 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand group-hover:scale-110 transition-transform">
              <i class="fas fa-shield-halved"></i>
            </div>
            <div>
              <p class="text-sm font-semibold text-stone-900">Vérifications</p>
              <p class="text-xs text-stone-400">Suivre mes demandes</p>
            </div>
          </router-link>
          <router-link v-if="auth.userRole === 'citoyen'" to="/citoyen/transactions" class="flex items-center gap-3 p-4 rounded-xl border border-stone-200 hover:border-brand-200 hover:bg-brand-50/30 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand group-hover:scale-110 transition-transform">
              <i class="fas fa-file-signature"></i>
            </div>
            <div>
              <p class="text-sm font-semibold text-stone-900">Transactions</p>
              <p class="text-xs text-stone-400">Historique</p>
            </div>
          </router-link>
          <router-link v-if="auth.userRole === 'notaire'" to="/notaire/dashboard" class="flex items-center gap-3 p-4 rounded-xl border border-stone-200 hover:border-brand-200 hover:bg-brand-50/30 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand group-hover:scale-110 transition-transform">
              <i class="fas fa-gavel"></i>
            </div>
            <div>
              <p class="text-sm font-semibold text-stone-900">Dashboard notaire</p>
              <p class="text-xs text-stone-400">Gérer mes dossiers</p>
            </div>
          </router-link>
          <router-link v-if="auth.userRole === 'geometre'" to="/geometre/missions" class="flex items-center gap-3 p-4 rounded-xl border border-stone-200 hover:border-brand-200 hover:bg-brand-50/30 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand group-hover:scale-110 transition-transform">
              <i class="fas fa-ruler-combined"></i>
            </div>
            <div>
              <p class="text-sm font-semibold text-stone-900">Missions</p>
              <p class="text-xs text-stone-400">Voir mes missions</p>
            </div>
          </router-link>
          <router-link to="/support/tickets" class="flex items-center gap-3 p-4 rounded-xl border border-stone-200 hover:border-brand-200 hover:bg-brand-50/30 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand group-hover:scale-110 transition-transform">
              <i class="fas fa-headset"></i>
            </div>
            <div>
              <p class="text-sm font-semibold text-stone-900">Support</p>
              <p class="text-xs text-stone-400">Obtenir de l'aide</p>
            </div>
          </router-link>
        </div>
      </div>
    </section>

    <!-- ===== FEATURES ===== -->
    <section class="py-24 bg-white">
      <div class="max-w-7xl mx-auto px-5 sm:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
          <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest text-brand bg-brand-50 mb-4">Plateforme complète</span>
          <h2 class="font-display font-extrabold text-3xl sm:text-4xl text-stone-900 mb-4">Tout ce qu'il faut pour sécuriser votre foncier</h2>
          <p class="text-stone-500 text-lg leading-relaxed">Des outils pensés pour les citoyens, les professionnels et les institutions.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
          <router-link
            v-for="f in features"
            :key="f.title"
            to="/parcelles"
            class="group p-7 rounded-2xl border border-stone-100 hover:border-brand-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 bg-stone-50 hover:bg-white"
          >
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-5 transition-transform group-hover:scale-110" :class="f.accent">
              <i :class="['fas', f.icon, 'text-lg']"></i>
            </div>
            <h3 class="font-display font-bold text-lg text-stone-900 mb-2">{{ f.title }}</h3>
            <p class="text-stone-500 text-sm leading-relaxed">{{ f.desc }}</p>
            <div class="mt-4 text-brand text-sm font-semibold opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1.5">
              Découvrir <i class="fas fa-arrow-right text-xs"></i>
            </div>
          </router-link>
        </div>
      </div>
    </section>

    <!-- ===== HOW IT WORKS ===== -->
    <section class="py-24" style="background: var(--bg);">
      <div class="max-w-7xl mx-auto px-5 sm:px-8">
        <div class="text-center max-w-xl mx-auto mb-16">
          <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest text-gold-dark bg-gold/10 mb-4">Comment ça marche</span>
          <h2 class="font-display font-extrabold text-3xl sm:text-4xl text-stone-900">En 4 étapes simples</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 relative">
          <!-- Connecting line -->
          <div class="hidden lg:block absolute top-8 left-[12.5%] right-[12.5%] h-px bg-gradient-to-r from-transparent via-brand-100 to-transparent"></div>

          <div v-for="s in steps" :key="s.num" class="relative">
            <div class="w-16 h-16 rounded-2xl bg-brand flex items-center justify-center mx-auto mb-5 shadow-brand relative z-10">
              <span class="font-display font-extrabold text-white text-lg">{{ s.num }}</span>
            </div>
            <div class="text-center">
              <h3 class="font-display font-bold text-stone-900 mb-2">{{ s.title }}</h3>
              <p class="text-sm text-stone-500 leading-relaxed">{{ s.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== CTA FINAL ===== -->
    <section class="py-20" style="background: var(--brand-dark);">
      <div class="max-w-4xl mx-auto px-5 sm:px-8 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gold/20 flex items-center justify-center mx-auto mb-8">
          <i class="fas fa-shield-halved text-gold-light text-2xl"></i>
        </div>
        <h2 class="font-display font-extrabold text-3xl sm:text-5xl text-white mb-6 leading-tight">
          Protégez votre patrimoine<br/>
          <span style="background: linear-gradient(135deg, #f2c558, #e8a020); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">dès aujourd'hui</span>
        </h2>
        <p class="text-white/50 text-lg mb-10 max-w-xl mx-auto leading-relaxed">
          Rejoignez des milliers de Béninois qui font confiance à FoncierSecure pour sécuriser leurs biens immobiliers.
        </p>
        <div class="flex flex-wrap gap-4 justify-center">
          <router-link
            v-if="!isAuth"
            to="/auth/register"
            class="inline-flex items-center gap-2.5 font-display font-bold text-brand-dark py-4 px-10 rounded-xl text-lg transition-all hover:scale-[1.02]"
            style="background: linear-gradient(135deg, #f2c558, #e8a020); box-shadow: var(--shadow-gold);"
          >
            Créer un compte gratuit <i class="fas fa-arrow-right text-sm"></i>
          </router-link>
          <router-link to="/parcelles" class="inline-flex items-center gap-2 font-semibold text-white/70 hover:text-white border border-white/15 hover:border-white/30 py-4 px-8 rounded-xl transition-all">
            Explorer les parcelles
          </router-link>
        </div>
      </div>
    </section>

  </div>
</template>
