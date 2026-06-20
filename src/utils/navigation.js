export function goBack(router, fallback = '/') {
  if (window.history.length > 1) {
    router.go(-1)
    return
  }
  router.push(fallback)
}

export function confirmLogout() {
  return window.confirm('Voulez-vous vraiment vous deconnecter ?')
}