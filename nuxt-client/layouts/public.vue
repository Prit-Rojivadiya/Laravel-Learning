<template>
  <v-app id="inspire">
    <div>
      <v-app-bar
        app
        color="primary"
        dark
      >
        <v-toolbar-title class="white--text">
          {{ applicationTitle }}
        </v-toolbar-title>
      </v-app-bar>

      <nuxt />
    </div>
  </v-app>
</template>

<script>

import 'sweetalert2/dist/sweetalert2.css'

export default {
  head() {
    return {
      title: 'Public Results'
    }
  },
  data: () => ({
    drawer: null,
    right: null,
    activeTab: null,
    selected: []
  }),
  computed: {
    applicationTitle () {
      return this.$store.state.applicationTitle
    }
  }
}
</script>
