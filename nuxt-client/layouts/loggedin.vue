<template>
  <v-app id="inspire">
    <v-navigation-drawer v-model="drawer" app width="300">
      <v-list-item class="title" style="text-align: center">
        <v-list-item-content>
          <v-list-item-title v-if="!isProduction" style="color:red">TESTING SITE</v-list-item-title>
          <v-list-item-title>{{ tenant.name }}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <v-list-item>
        <v-list-item-content>
          <v-list-item-title class="title">
            <v-img :src="tenant.logo" max-height="60" contain v-if="tenant.logo" />
            <v-img src="/img/logo_ntc.jpg" max-height="60" contain v-else-if="tenant.name == 'NTC'" />
            <v-img src="/img/logo.png" max-height="60" contain v-else/>
          </v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <v-list-item>
        <v-list-item-content>
          <v-list-item-title style="text-align: center">Welcome {{ $auth.user.name }}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <v-list dense>
        <v-list-item nuxt :to="`/`">
          <v-list-item-icon>
            <v-icon>mdi-home</v-icon>
          </v-list-item-icon>
          <v-list-item-title>Home</v-list-item-title>
        </v-list-item>

        <v-list-group prepend-icon="mdi-account-group" v-if="isAdmin" :value="false">
          <template v-slot:activator>
            <v-list-item-title>Admin</v-list-item-title>
          </template>
          <v-list-item v-if="showTenants" nuxt :to="'/tenants'">
            <v-list-item-title>Tenants</v-list-item-title>
          </v-list-item>
          <v-list-item nuxt :to="'/users'">
            <v-list-item-title>Users</v-list-item-title>
          </v-list-item>
          <v-list-item nuxt :to="'/integrations'">
            <v-list-item-title>Integrations</v-list-item-title>
          </v-list-item>
        </v-list-group>

        <v-list-group prepend-icon="mdi-chart-multiple" :value="false"  v-if="showFleetManagement">
          <template v-slot:activator>
            <v-list-item-title>Reports</v-list-item-title>
          </template>
          <v-list-item nuxt :to="'/reports/invoice_summary'">
            <v-list-item-title>Invoice Summary</v-list-item-title>
          </v-list-item>
          <v-list-item nuxt :to="'/reports/cost_per_mile_summary'">
            <v-list-item-title>Cost Per Mile Summary</v-list-item-title>
          </v-list-item>
          <v-list-item nuxt :to="'/reports/cost_per_mile_detailed'">
            <v-list-item-title>Cost Per Mile Detailed</v-list-item-title>
          </v-list-item>
          <v-list-item nuxt :to="'/reports/fuel_tax'">
            <v-list-item-title>Fuel Tax Report</v-list-item-title>
          </v-list-item>
          <!--          <v-list-item nuxt :to="'#'">-->
          <!--            <v-list-item-title>Cost Comparison</v-list-item-title>-->
          <!--          </v-list-item>-->
        </v-list-group>

        <v-list-group prepend-icon="mdi-family-tree" :value="false"  v-if="showFleetManagement">
          <template v-slot:activator>
            <v-list-item-title>Fleet Profile</v-list-item-title>
          </template>
          <v-list-item nuxt :to="'/clients'">
            <v-list-item-title>Accounts</v-list-item-title>
          </v-list-item>
          <v-list-item nuxt :to="'/branches'">
            <v-list-item-title>Branches</v-list-item-title>
          </v-list-item>
          <v-list-item nuxt :to="'/fleets'">
            <v-list-item-title>Fleets</v-list-item-title>
          </v-list-item>
          <v-list-item nuxt :to="'/vehicles'">
            <v-list-item-title>Vehicles</v-list-item-title>
          </v-list-item>
        </v-list-group>

        <v-list-group prepend-icon="mdi-truck-fast" :value="false"  v-if="showFleetManagement">
          <template v-slot:activator>
            <v-list-item-title>Fleet Management</v-list-item-title>
          </template>
          <v-list-item nuxt :to="'/preventive_maintenances'">
            <v-list-item-title>Preventive Maintenance</v-list-item-title>
          </v-list-item>
          <v-list-item nuxt :to="'/repair_orders'">
            <v-list-item-title>Repair Orders</v-list-item-title>
          </v-list-item>
        </v-list-group>

        <v-list-group prepend-icon="mdi-database-import" :value="false"  v-if="showFleetManagement">
          <template v-slot:activator>
            <v-list-item-title>Data Imports</v-list-item-title>
          </template>
          <v-list-item nuxt :to="'/file_imports'">
            <v-list-item-title>Fuel Transactions</v-list-item-title>
          </v-list-item>
        </v-list-group>

        <v-list-group prepend-icon="mdi-folder-cog-outline" :value="false">
          <template v-slot:activator>
            <v-list-item-title>Setup</v-list-item-title>
          </template>
          <v-list-item v-if="isAdmin" nuxt :to="'/vendors'">
            <v-list-item-title>Vendors</v-list-item-title>
          </v-list-item>
          <v-list-item nuxt :to="'/vehicle_types'">
            <v-list-item-title>Vehicle Types</v-list-item-title>
          </v-list-item>
          <v-list-item nuxt :to="'/repair_order_types'">
            <v-list-item-title>Repair Order Types</v-list-item-title>
          </v-list-item>
          <v-list-item nuxt :to="'/line_item_types'">
            <v-list-item-title>Line Item Types</v-list-item-title>
          </v-list-item>
          <v-list-item nuxt :to="'/line_item_categories'">
            <v-list-item-title>Line Item Categories</v-list-item-title>
          </v-list-item>
          <v-list-item v-if="isAdmin" nuxt :to="'/preventive_maintenance_templates'">
            <v-list-item-title>PM Templates</v-list-item-title>
          </v-list-item>
          <v-list-item nuxt :to="'/engine_manufacturers'">
            <v-list-item-title>Engine Manufacturers</v-list-item-title>
          </v-list-item>
        </v-list-group>

        <v-list-item
          v-for="item in items"
          :key="item.title"
          :to="item.to"
          link
          multiple
        >
          <v-list-item-icon>
            <v-icon>{{ item.icon }}</v-icon>
          </v-list-item-icon>

          <v-list-item-content>
            <v-list-item-title>{{ item.title }}</v-list-item-title>
          </v-list-item-content>
        </v-list-item>


<!--        <v-list-item href="https://google.com/" target="_blank">-->
<!--          <v-list-item-icon>-->
<!--            <v-icon>mdi-help-circle</v-icon>-->
<!--          </v-list-item-icon>-->

<!--          <v-list-item-content>-->
<!--            <v-list-item-title>Support</v-list-item-title>-->
<!--          </v-list-item-content>-->
<!--        </v-list-item>-->

        <v-list-item @click="performLogout()">
          <v-list-item-icon>
            <v-icon>mdi-logout</v-icon>
          </v-list-item-icon>

          <v-list-item-content>
            <v-list-item-title>
              <template v-if="isImpersonating">Stop Impersonating</template>
              <template v-else>Logout</template>
            </v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-app-bar
      app
      color="primary"
      dark
    >
      <v-app-bar-nav-icon @click.stop="drawer = !drawer" />
      <v-toolbar-title class="white--text" />
      <v-img class="tz-header-bar-logo" src="/img/logo_white.png" max-height="60" contain />

    </v-app-bar>

    <v-main>
      <v-container fluid>
        <nuxt />
      </v-container>
    </v-main>
  </v-app>
</template>

<style lang="scss">
</style>

<script>
import { mapActions, mapState } from 'vuex'

export default {
  middleware: 'auth',
  head() {
    return {
      title: 'TranzIT Fleet Management'
    }
  },
  watch: {
  },
  data: () => ({
    tenant: {
      name: null,
      logo: null
    },
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
      { title: 'Profile', icon: 'mdi-account', to: '/profile' }
    ],
    right: null,
    activeTab: null,
    selected: [],
    isAdmin: false,
    showTenants: false,
    showFleetManagement: true
  }),
  computed: {
    isImpersonating () {
      return localStorage.getItem('impersonating')
    },
    isProduction () {
      return process.env.apiUrl === 'https://api.tranzitfleet.com/api'
    }
  },
  created () {
    this.getTenant()
    this.$laravel.setPermissions(this.$auth.user.permissions)
    this.$laravel.setRoles(this.$auth.user.userRoles)
    if (this.$laravel.hasPermission('manage tenant')) {
      this.isAdmin = true
    }
    if (this.$laravel.hasRole('super-admin')) {
      this.showTenants = true
    }
  },
  methods: {
    async getTenant() {
      const response = await this.$axios.$get(`/tenants/name/${this.$auth.user.tenant_id}`)
      this.tenant.name = response.name
      this.tenant.logo = response.logo_path
      if (this.tenant.name === 'TranzIT') {
        this.showTenants = true
        this.showFleetManagement = false
      }
    },
    async performLogout () {
      if (localStorage.getItem('impersonating')) {
        await this.$axios.$post(`/exit-impersonation`)

        localStorage.removeItem('impersonating')

        window.location = '/'
      } else {
        this.$auth.logout()
      }
    }
  }
}
</script>

<style scoped>
.tz-header-bar-logo {
  max-width: 140px;
  margin-left: auto
}

</style>
