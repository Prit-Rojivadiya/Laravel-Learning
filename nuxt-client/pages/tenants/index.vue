<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Tenants</h2>
        </v-col>
      </v-row>
    </div>

    <TenantsGridComponent
      v-if="showTenants"
      :showAddTenantInGrid="showAddTenantInGrid"
      v-model="showTenants"
    />

    <ManageTenantComponent
      v-if="addingTenant"
      v-model="addingTenant"
      @tenant-saved="refresh"
    />

  </div>
</template>

<script>

import TenantsGridComponent from '~/components/tenants/TenantsGrid'
import ManageTenantComponent from '~/components/tenants/ManageTenant'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    TenantsGridComponent,
    ManageTenantComponent
  },
  data: () => ({
    addingTenant: false,
    showTenants: true,
    showAddTenantInGrid: false,
  }),
  created () {
    if (this.$laravel.hasRole('super-admin')) {
      this.showAddTenantInGrid = true
    }
  },
  methods: {
    addTenant () {
      this.addingTenant = true
    },
    refresh () {
      this.showTenants = false
      this.$nextTick().then(() => {
        // Add the component back in
        this.showTenants = true
      })
    }
  }
}
</script>
