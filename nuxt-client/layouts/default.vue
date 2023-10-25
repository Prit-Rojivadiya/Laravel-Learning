<template>
  <v-app id="inspire">
    <nuxt />
  </v-app>
</template>

<style lang="scss">
</style>

<script>
import 'sweetalert2/dist/sweetalert2.css'

export default {
  head() {
    return {
      title: 'SSSF'
    }
  },
  data: () => ({
    drawer: null,
    admins: [
      ['Management', 'mdi-account-multiple-outline'],
      ['Settings', 'mdi-settings']
    ],
    cruds: [
      ['Create', 'mdi-plus'],
      ['Read', 'mdi-insert-drive-file'],
      ['Update', 'mdi-update'],
      ['Delete', 'mdi-delete']
    ],
    items: [
      { title: 'Home', icon: 'mdi-home' },
      { title: 'Team', icon: 'mdi-account-group' }
    ],
    right: null,
    activeTab: null,
    selected: []
  })
}
</script>
