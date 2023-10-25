<template>
  <v-navigation-drawer
    v-model="DRAWER_STATE"

    app
    :mini-variant="!DRAWER_STATE"
    :width="sidebarWidth"
    :permanent="$vuetify.breakpoint.mdAndUp"
    :temporary="$vuetify.breakpoint.smAndDown"
    :mini-variant-width="sidebarMinWidth"
    :class="{'drawer-mini': !DRAWER_STATE}"
  >
    <template slot="prepend">
      <v-img src="/img/sssf-logo.png" max-height="60" contain />
    </template>
    <v-list>
      <template v-for="(item, i) in items">
        <v-row
          v-if="item.heading"
          :key="item.heading"
          align="center"
        >
          <v-col cols="6">
            <span
              style="padding-lLeaderboardControllereft: 32px"
              class="text-body-1 subheader"
              :class="(item.heading && DRAWER_STATE) ? 'show ' : 'hide'"
            >
              {{ item.heading }}
            </span>
          </v-col>
          <v-col
            cols="6"
            class="text-center"
          />
        </v-row>
        <v-divider
          v-else-if="item.divider"
          :key="i"
          dark
          class="my-4"
        />
        <v-list-group
          v-else-if="item.children && DRAWER_STATE"
          :key="item.title"
          v-model="item.model"
          color="primary"
          append-icon=""
        >
          <template v-slot:prependIcon>
            <v-icon size="28">
              mdi-image-filter-none
            </v-icon>
          </template>
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title
                class="grey--text"
              >
                {{ item.title }}
              </v-list-item-title>
            </v-list-item-content>
          </template>
          <v-list-item
            v-for="(child, i) in item.children"
            :key="i"
            :to="child.link"
            link
          >
            <v-list-item-action v-if="child.icon">
              <v-icon size="">
                {{ child.icon }}
              </v-icon>
            </v-list-item-action>
            <v-list-item-content>
              <v-list-item-title class="grey--text">
                {{ child.title }}
              </v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-group>
        <v-list-item
          v-else
          :key="item.text"
          color="primary"
          :to="item.link === '#' ? null : item.link"
          link
        >
          <v-list-item-action>
            <v-icon
              size="28"
              :color="item.color ? item.color : ''"
            >
              {{ item.icon }}
            </v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title
              class="grey--text"
              link
            >
              {{ item.title }}
            </v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </template>
    </v-list>
  </v-navigation-drawer>
</template>

<style lang="scss" scoped>

  .v-navigation-drawer {
    top: 64px!important;
    height: calc(100vh - 64px)!important;

    .v-navigation-drawer__content {
      &::-webkit-scrollbar {
        width: 6px;
      }
      &::-webkit-scrollbar-track {
        background: transparent;
      }
      &::-webkit-scrollbar-thumb {
        border-radius: 36px;
        border: none;
      }
    }
    .v-list-item:not(.v-list-item--active) {
      .v-icon {
      }
    }
    .v-list-item--active {
      .v-list-item__content {
        .v-list-item__title {
        }
      }
    }

    &.drawer-mini {
      .v-list {
        div, a {
          &.v-list-item {
            transition: 300ms all;
            padding-left: 4 / 4;
          }
        }
      }
    }
    .v-list {
      div, a {
        &.v-list-item {
          padding-left: 4;
        }
        a.v-list-item {
          padding-left: 4 * 2;
        }
      }
    }

    .subheader {
      transition: 300ms all;
    }
    .hide {
      opacity: 0;
    }
    .show {
      opacity: 1;
    }
  }

  .v-navigation-drawer--temporary.v-navigation-drawer--clipped {
    z-index: 5;
  }
</style>
<script>
import { mapActions, mapState } from 'vuex'

export default {
  props: {
    source: String
  },
  data () {
    return {
      items: [
        { title: 'Dashboard', icon: 'mdi-home', link: '/' },
        { title: 'Typography', icon: 'mdi-format-size', link: '/typography' },
        { title: 'Tables', icon: 'mdi-grid-large', link: '/tables' },
        { title: 'Notifications', icon: 'mdi-bell-outline', link: '/notifications' },
        {
          title: 'UI Elements',
          icon: 'mdi-image-filter-none',
          link: '/icons',
          model: false,
          children: [
            { title: 'Icons', icon: 'mdi-circle-small', link: '/icons' },
            { title: 'Charts', icon: 'mdi-circle-small', link: '/charts' },
            { title: 'Maps', icon: 'mdi-circle-small', link: '/maps' }
          ]
        },
        { divider: true },
        { heading: 'HELP' },
        { title: 'Library', icon: 'mdi-book-variant-multiple' },
        { title: 'Support', icon: 'mdi-forum' },
        { title: 'FAQ', icon: 'mdi-help-circle-outline' },
        { divider: true },
        { heading: 'PROJECTS' },
        { title: 'My recent', icon: 'mdi-circle-medium', color: 'warning' },
        { title: 'Starred', icon: 'mdi-circle-medium', color: 'primary' },
        { title: 'Background', icon: 'mdi-circle-medium', color: 'error' }

      ],
      sidebarWidth: 240,
      sidebarMinWidth: 96
    }
  },
  computed: {
    ...mapState(['drawer']),
    DRAWER_STATE: {
      get () {
        return this.drawer
      },
      set (newValue) {
        if (newValue === this.drawer) { return }
        this.TOGGLE_DRAWER()
      }
    }
  },
  methods: {
    ...mapActions([ 'TOGGLE_DRAWER' ])
  }
}
</script>
