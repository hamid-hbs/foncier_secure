import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    component: () => import('@/layouts/PublicLayout.vue'),
    children: [
      { path: '', name: 'Landing', component: () => import('@/views/public/LandingPage.vue') },
      { path: 'accueil', name: 'Home', component: () => import('@/views/public/HomePage.vue') },
      { path: 'auth/login', name: 'Login', component: () => import('@/views/auth/LoginPage.vue'), meta: { guest: true } },
      { path: 'auth/register', name: 'Register', component: () => import('@/views/auth/RegisterPage.vue'), meta: { guest: true } },
      { path: 'auth/forgot-password', name: 'ForgotPassword', component: () => import('@/views/auth/ForgotPasswordPage.vue'), meta: { guest: true } },
      { path: 'auth/reset-password', name: 'ResetPassword', component: () => import('@/views/auth/ResetPasswordPage.vue'), meta: { guest: true } },
      { path: 'parcelles', name: 'PublicParcelles', component: () => import('@/views/public/ParcellesListPage.vue') },
      { path: 'parcelles/:id', name: 'PublicParcelleDetail', component: () => import('@/views/public/ParcelleDetailPage.vue') },
      { path: 'cartographie', name: 'Cartographie', component: () => import('@/views/public/CartographiePage.vue') },
      { path: 'observatoire', name: 'Observatoire', component: () => import('@/views/public/ObservatoirePage.vue') },
      { path: 'professionnels', name: 'Professionnels', component: () => import('@/views/public/ProfessionnelsListPage.vue') },
      { path: 'professionnels/:id', name: 'ProfessionnelDetail', component: () => import('@/views/public/ProfessionnelDetailPage.vue') },
      { path: 'blockchain/verifier', name: 'BlockchainVerifier', component: () => import('@/views/public/BlockchainVerifierPage.vue') },
    ],
  },
  {
    path: '/',
    component: () => import('@/layouts/AuthLayout.vue'),
    children: [
      { path: 'mon-profil', name: 'Profil', component: () => import('@/views/auth/ProfilePage.vue'), meta: { auth: true } },
      { path: 'notifications', name: 'Notifications', component: () => import('@/views/auth/NotificationsPage.vue'), meta: { auth: true } },

      { path: 'tableau-de-bord', name: 'CitoyenDashboard', component: () => import('@/views/citoyen/DashboardPage.vue'), meta: { auth: true } },

      // Citoyen Parcelles
      { path: 'citoyen/parcelles', name: 'MesParcelles', component: () => import('@/views/citoyen/MesParcellesPage.vue'), meta: { auth: true, role: 'citoyen' } },
      { path: 'citoyen/parcelles/creer', name: 'CreerParcelle', component: () => import('@/views/citoyen/ParcelleFormPage.vue'), meta: { auth: true, role: 'citoyen' } },
      { path: 'citoyen/parcelles/:id/modifier', name: 'ModifierParcelle', component: () => import('@/views/citoyen/ParcelleFormPage.vue'), meta: { auth: true, role: 'citoyen' } },
      { path: 'citoyen/parcelles/:id/documents', name: 'DocumentsParcelle', component: () => import('@/views/citoyen/DocumentsParcellePage.vue'), meta: { auth: true, role: 'citoyen' } },
      { path: 'citoyen/parcelles/:id', name: 'CitoyenParcelleDetail', component: () => import('@/views/citoyen/ParcelleDetailPage.vue'), meta: { auth: true, role: 'citoyen' } },

      // Citoyen Verifications (lecture seule)
      { path: 'citoyen/verifications', name: 'Verifications', component: () => import('@/views/citoyen/MesVerificationsPage.vue'), meta: { auth: true, role: 'citoyen' } },
      { path: 'citoyen/verifications/:id', name: 'VerificationDetail', component: () => import('@/views/citoyen/VerificationDetailPage.vue'), meta: { auth: true, role: 'citoyen' } },

      // Citoyen Transactions (read-only — notaire creates)
      { path: 'citoyen/transactions', name: 'Transactions', component: () => import('@/views/citoyen/TransactionsPage.vue'), meta: { auth: true, role: 'citoyen' } },
      { path: 'citoyen/transactions/:id', name: 'TransactionDetail', component: () => import('@/views/citoyen/TransactionDetailPage.vue'), meta: { auth: true, role: 'citoyen' } },

      // Citoyen Demandes d'achat
      { path: 'citoyen/demandes-achat', name: 'DemandesAchat', component: () => import('@/views/citoyen/DemandesAchatPage.vue'), meta: { auth: true, role: 'citoyen' } },
      { path: 'citoyen/demandes-achat/creer', name: 'NouvelleDemandeAchat', component: () => import('@/views/citoyen/NouvelleDemandeAchatPage.vue'), meta: { auth: true, role: 'citoyen' } },
      { path: 'citoyen/demandes-achat/:id', name: 'DemandeAchatDetail', component: () => import('@/views/citoyen/DemandeAchatDetailPage.vue'), meta: { auth: true, role: 'citoyen' } },

      // Citoyen Rendez-vous
      { path: 'citoyen/rendez-vous', name: 'RendezVous', component: () => import('@/views/citoyen/RendezVousPage.vue'), meta: { auth: true, role: 'citoyen' } },

      // Citoyen Coffre
      { path: 'citoyen/coffre', name: 'Coffre', component: () => import('@/views/citoyen/CoffreDossiersPage.vue'), meta: { auth: true, role: 'citoyen' } },
      { path: 'citoyen/coffre/dossiers/:id', name: 'CoffreDossierDetail', component: () => import('@/views/citoyen/CoffreDossierDetailPage.vue'), meta: { auth: true, role: 'citoyen' } },

      // Citoyen Recherche
      { path: 'citoyen/recherche', name: 'Recherche', component: () => import('@/views/citoyen/RecherchePage.vue'), meta: { auth: true, role: 'citoyen' } },

      // Support Tickets (tous utilisateurs connectes)
      { path: 'support/tickets', name: 'SupportTickets', component: () => import('@/views/citoyen/SupportTicketsPage.vue'), meta: { auth: true } },
      { path: 'support/tickets/creer', name: 'SupportTicketCreer', component: () => import('@/views/citoyen/SupportTicketFormPage.vue'), meta: { auth: true } },
      { path: 'support/tickets/:id', name: 'SupportTicketDetail', component: () => import('@/views/citoyen/SupportTicketDetailPage.vue'), meta: { auth: true } },

      // Géomètre
      { path: 'geometre/dashboard', name: 'GeometreDashboard', component: () => import('@/views/geometre/GeometreDashboardPage.vue'), meta: { auth: true, role: 'geometre' } },
      { path: 'geometre/missions', name: 'Missions', component: () => import('@/views/geometre/MissionsPage.vue'), meta: { auth: true, role: 'geometre' } },
      { path: 'geometre/verifications/:id', name: 'GeometreVerificationDetail', component: () => import('@/views/citoyen/VerificationDetailPage.vue'), meta: { auth: true, role: 'geometre' } },
      { path: 'geometre/verifications/:id/rapport', name: 'DepotRapport', component: () => import('@/views/geometre/DepotRapportPage.vue'), meta: { auth: true, role: 'geometre' } },

      // Notaire
      { path: 'notaire/dashboard', name: 'NotaireDashboard', component: () => import('@/views/notaire/NotaireDashboardPage.vue'), meta: { auth: true, role: 'notaire' } },
      { path: 'notaire/demandes-achat', name: 'NotaireDemandesAchat', component: () => import('@/views/citoyen/DemandesAchatPage.vue'), meta: { auth: true, role: 'notaire' } },
      { path: 'notaire/demandes-achat/:id', name: 'NotaireDemandeAchatDetail', component: () => import('@/views/citoyen/DemandeAchatDetailPage.vue'), meta: { auth: true, role: 'notaire' } },
      { path: 'notaire/rendez-vous', name: 'NotaireRendezVous', component: () => import('@/views/citoyen/RendezVousPage.vue'), meta: { auth: true, role: 'notaire' } },
      { path: 'notaire/transactions', name: 'TransactionsNotaire', component: () => import('@/views/notaire/TransactionsNotairePage.vue'), meta: { auth: true, role: 'notaire' } },
      { path: 'notaire/transactions/creer', name: 'NotaireTransactionCreer', component: () => import('@/views/notaire/NotaireCreerTransactionPage.vue'), meta: { auth: true, role: 'notaire' } },
      { path: 'notaire/transactions/:id', name: 'TransactionNotaireDetail', component: () => import('@/views/notaire/TransactionNotaireDetailPage.vue'), meta: { auth: true, role: 'notaire' } },
      { path: 'notaire/transactions/:id/intervenants', name: 'NotaireInviter', component: () => import('@/views/notaire/NotaireInviterPage.vue'), meta: { auth: true, role: 'notaire' } },
      { path: 'notaire/transactions/:id/verifier-identite', name: 'NotaireVerifierIdentite', component: () => import('@/views/notaire/NotaireVerifierIdentitePage.vue'), meta: { auth: true, role: 'notaire' } },
      { path: 'notaire/transactions/:id/planifier-rendezvous', name: 'NotairePlanifierRendezVous', component: () => import('@/views/notaire/NotairePlanifierRendezVousPage.vue'), meta: { auth: true, role: 'notaire' } },
      // Admin Support Tickets
      { path: 'admin/support/tickets', name: 'AdminSupportTickets', component: () => import('@/views/admin/SupportTicketsPage.vue'), meta: { auth: true, role: 'admin' } },
      { path: 'admin/support/tickets/:id', name: 'AdminSupportTicketDetail', component: () => import('@/views/admin/SupportTicketDetailPage.vue'), meta: { auth: true, role: 'admin' } },

      // Admin
      { path: 'admin/dashboard', name: 'AdminDashboard', component: () => import('@/views/admin/DashboardPage.vue'), meta: { auth: true, role: 'admin' } },
      { path: 'admin/users', name: 'AdminUsers', component: () => import('@/views/admin/UsersPage.vue'), meta: { auth: true, role: 'admin' } },
      { path: 'admin/role-requests', name: 'AdminRoleRequests', component: () => import('@/views/admin/RoleRequestsPage.vue'), meta: { auth: true, role: 'admin' } },
      { path: 'admin/localisation', name: 'AdminLocalisation', component: () => import('@/views/admin/LocalisationPage.vue'), meta: { auth: true, role: 'admin' } },
      { path: 'admin/users/new', name: 'AdminUserCreate', component: () => import('@/views/admin/UserFormPage.vue'), meta: { auth: true, role: 'admin' } },
      { path: 'admin/blockchain', name: 'AdminBlockchain', component: () => import('@/views/admin/BlockchainExplorerPage.vue'), meta: { auth: true, role: 'admin' } },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const { useAuthStore } = await import('@/stores/auth')
  const auth = useAuthStore()

  if (to.meta.auth && !auth.isAuthenticated) {
    return next({ name: 'Login', query: { redirect: to.fullPath } })
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return next({ name: 'Profil' })
  }

  if (to.meta.role) {
    const allowed = to.meta.role.split(',')
    if (!allowed.includes(auth.user?.role)) {
      return next({ name: 'Profil' })
    }
  }

  next()
})

export default router
