<template>
  <v-card class="mt-15">
    <v-row>
      <v-col cols="12">
        <v-card-title>
          Users
        </v-card-title>
      </v-col>
      <v-col>
        <v-btn v-if="showAddUserInGrid" small color="info" style="float: right" class="my-4 mr-3" @click="addUser">Add New User</v-btn>
      </v-col>
    </v-row>

    <div class="px-3 mb-5">
      <v-row>
        <v-col cols="4">
          <v-text-field
            v-model="filterByName"
            label="Search Name"
            dense
            @blur="getDataFromApi"
          />
        </v-col>
        <v-col cols="4">
          <v-text-field
            v-model="filterByEmail"
            label="Search Email"
            dense
            @blur="getDataFromApi"
          />
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="4" v-if="showMultiTenants">
          <ModelNameSearchSelect
            v-if="showMultiTenants"
            v-model="filteredTenant"
            dense
            :prefill="true"
            :search_label="'Tenant'"
            :api_url_base="'tenants'"
            :includeAllSelection="true"
            :rules="[v => !!v || 'Please select a tenant']"
            @input="updateSelectedTenant"
          />
        </v-col>
        <v-col>
          <v-btn small color="primary" style="float: right" class="ml-5 mb-2" :loading="loading" @click="getDataFromApi">Search</v-btn>
          <v-btn small style="float: right;" class="ml-5 mb-2" :disabled="loading" @click="clearFilters">Clear</v-btn>
        </v-col>
      </v-row>
    </div>

    <Grid
      class="compact-grid"
      v-if="showGrid"
      :height="700"
      :kGridData="gridData"
      :kSchema="gridSchema"
      :kColumns="gridColumns"
      :kGroup="gridGroup"
      :kAggregate="gridAggregate"
      :kToolBar="['excel', 'pdf']"
      :kPageSize="100"
    />

    <ManageUserComponent
      v-if="addingUser"
      v-model="addingUser"
      @user-saved="getDataFromApi"
    />

  </v-card>

</template>

<script>

import ModelNameSearchSelect from '~/components/forms/ModelNameSearchSelect'
import ManageUserComponent from '~/components/users/ManageUser'
import Grid from '~/components/kendo/grid2/Grid'

export default {
  name: "UsersGrid",
  props: ['value', 'showAddUserInGrid', 'scopeToTenant'],
  components: {
    ManageUserComponent,
    ModelNameSearchSelect,
    Grid
  },
  data: () => ({
    loading: false,
    addingUser: false,
    filterByName: null,
    filterByEmail: null,
    showMultiTenants: false,
    filteredTenantId: null,
    filteredTenant: { id: 'all', name: "All" },
    showGrid: false,
    gridData: [],
    gridColumns: [],
    gridSchema: {},
    gridGroup: null,
    gridAggregate: [],
    kColumnWidthDefault: "225px",
    kDefaultContains: { cell: { operator: 'contains' } }
  }),
  async created () {
    if (this.scopeToTenant) {
      this.showMultiTenants = false
      this.filteredTenant = this.scopeToTenant
      this.filteredTenantId = this.scopeToTenant.id
      this.getDataFromApi()
    }
    else if (this.$laravel.hasRole('super-admin')) {
      this.showMultiTenants = true
    }
  },
  methods: {
    async getDataFromApi() {
      this.loading = true
      this.showGrid = false
      const response = await this.$axios.$get('users', {
        params: {
          filterByName: this.filterByName,
          filterByEmail: this.filterByEmail,
          filterByTenant: (this.filteredTenantId && this.filteredTenantId != 'all') ? this.filteredTenantId : null
        }
      })

      this.gridData = response
      this.gridColumns = []

      this.gridColumns.push({field: 'name', title: 'Name', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'email', title: 'Email', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'tenantName', title: 'Tenant', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, aggregates: ["count"], groupHeaderTemplate: "#=data.value# (#= count#)"})
      //this.gridColumns.push({field: 'roles', title: 'Role', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, aggregates: ["count"], groupHeaderTemplate: "#=data.value# (#= count#)"})
      this.gridColumns.push({field: 'created_at', title: 'Created Date', hidden: true, format: '{0:MM/dd/yyyy}', width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'updated_at', title: 'Last Updated Date', hidden: true, format: '{0:MM/dd/yyyy}', width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'impersonate', title: 'Impersonate', hidden: false, template: this.impersonateUser('id'), width: '150px'})
      this.gridColumns.push({field: 'edit', title: 'Manage', hidden: false, template: this.openData('id'), width: '100px'})
      this.gridSchema = {
        model: {
          id: "id",
          fields: {
            name: {type: 'string'},
            email: {type: 'string'},
            tenantName: {type: 'string'},
            roles: {type: 'string'},
            created_at: {type: 'date'},
            updated_at: {type: 'date'}
          }
        }
      }
      // this.gridGroup = {
      //   field: 'tenantName', aggregates: [
      //     {field: 'tenantName', aggregate: "count"},
      //     {field: 'tenantName', aggregate: "sum"}
      //   ]
      // }
      this.gridAggregate = [
        { field: 'tenantName', aggregate: 'count' }
      ]

      this.refresh()
      this.loading = false
    },
    openData (fieldKey) {
      return `# if(typeof data.${fieldKey} != 'undefined') { # <a href="/users/#=data.${fieldKey}#">Manage</a> # } #`
      // this.$router.push(`/users/${dataItem.id}`)
    },
    impersonateUser (fieldKey) {
      return `# if(typeof data.${fieldKey} != 'undefined') { # <a href="/impersonate?id=#=data.${fieldKey}#">Impersonate</a> # } #`
      // this.$router.push(`/impersonate?id=${this.user.id}`)
    },
    clearFilters () {
      this.filterByName = null
      this.filterByEmail = null
      this.filteredTenant = { id: 'all', name: 'All' }
      this.filteredTenantId = null
      // this.getDataFromApi()
    },
    addUser () {
      this.addingUser = true
    },
    refresh () {
      this.showGrid = false
      this.$nextTick().then(() => {
        this.showGrid = true
      })
    },
    updateSelectedTenant () {
      this.showMultiTenants = false
      if (this.filteredTenant) {
        if (this.filteredTenant.id !== 'all') {
          this.filteredTenantId = this.filteredTenant.id
        }
        else {
          this.filteredTenantId = null
        }
      }
      this.$nextTick().then(() => {
        this.showMultiTenants = true
      })
    }
  }
}
</script>

<style scoped>

</style>
