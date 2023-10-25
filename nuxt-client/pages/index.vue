<template>
  <v-content />
</template>

<style lang="scss">
</style>

<script>
export default {
  data: () => ({ }),
  created () {
    if (this.$auth.user) {
      this.$router.push('/dashboard')

      return
    }
    this.$router.push('/dashboard')
  }
}
</script>
