<template>
  <v-app dark v-if="show">
    <h1 v-if="error.statusCode === 404">
      {{ pageNotFound }}
    </h1>
    <h1 v-else>
      {{ otherError }}
    </h1>
    <NuxtLink to="/">
      Home page
    </NuxtLink>
  </v-app>
</template>

<script>
export default {
  layout: 'empty',
  props: {
    error: {
      type: Object,
      default: null
    }
  },
  head () {
    const title =
      this.error.statusCode === 404 ? this.pageNotFound : this.otherError
    return {
      title
    }
  },
  created () {
    if (this.error.message === 'Both token and refresh token have expired. Your request was aborted.') {
      window.location = '/login'
      return
    }

    if (!this.$auth.user) {
      window.location = '/login'

      return
    }

    this.show = true
  },
  data () {
    return {
      show: false,
      pageNotFound: '404 Not Found',
      otherError: 'An error occurred'
    }
  }
}
</script>

<style scoped>
h1 {
  font-size: 20px;
}
</style>
