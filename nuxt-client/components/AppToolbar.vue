<template>
  <v-toolbar
    color="primary"
    fixed
    dark
    app
  >
    <v-toolbar-title class="ml-0 pl-3">
      <v-toolbar-side-icon @click.stop="toggleDrawer()" />
    </v-toolbar-title>
    <v-text-field
      flat
      solo-inverted
      prepend-icon="search"
      label="Search"
      class="hidden-sm-and-down"
    />
    <v-spacer />
    <v-btn icon href="https://github.com/moeddami/nuxt-material-admin">
      <v-icon>fa-2x fa-github</v-icon>
    </v-btn>
    <v-btn icon @click="handleFullScreen()">
      <v-icon>fullscreen</v-icon>
    </v-btn>
    <v-menu
      offset-y
      origin="center center"
      class="elelvation-1"
      :nudge-right="140"
      :nudge-bottom="14"
      transition="scale-transition"
    >
      <v-btn slot="activator" icon flat>
        <v-badge color="red" overlap>
          <span slot="badge">3</span>
          <v-icon medium>
            notifications
          </v-icon>
        </v-badge>
      </v-btn>
    </v-menu>
    <v-menu offset-y origin="center center" :nudge-right="140" :nudge-bottom="10" transition="scale-transition">
      <v-btn slot="activator" icon large flat>
        <v-avatar size="30px" />
      </v-btn>
      <v-list class="pa-0">
        <v-list-tile
          v-for="(item,index) in items"
          :key="index"
          :to="!item.href ? { name: item.name } : null"
          :href="item.href"
          ripple="ripple"
          :disabled="item.disabled"
          :target="item.target"
          rel="noopener"
          @click="item.click"
        >
          <v-list-tile-action v-if="item.icon">
            <v-icon>{{ item.icon }}</v-icon>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title>{{ item.title }}</v-list-tile-title>
          </v-list-tile-content>
        </v-list-tile>
      </v-list>
    </v-menu>
  </v-toolbar>
</template>
<script>
export default {
  name: 'AppToolbar',
  data () {
    return {
      items: [
        {
          icon: 'account_circle',
          href: '#',
          title: 'Profile',
          click: (e) => {
            console.log(e)
          }
        },
        {
          icon: 'settings',
          href: '#',
          title: 'Settings',
          click: (e) => {
            console.log(e)
          }
        },
        {
          icon: 'fullscreen_exit',
          href: '#',
          title: 'Logout',
          click: this.handleLogout
        }
      ]
    }
  },
  computed: {
    toolbarColor () {
      return this.$vuetify.options.extra.mainNav
    }
  },
  methods: {
    toggleDrawer () {
      this.$store.commit('toggleDrawer')
    }
  }
}
</script>
