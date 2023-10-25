<template>
  <div>Loading...</div>
</template>

<script>
export default {
  layout: 'auth',
  async created () {
    const result = await this.$axios.$post(`/impersonate/${this.$route.query.id}`)

    localStorage.setItem('impersonating', 'true')
    window.location = '/dashboard'
  }
}
</script>

<style scoped>

</style>
