<template>
  <v-card class="mt-15">
    <v-row>
      <v-col cols="12">
        <v-card-title>
          Tenants
        </v-card-title>
      </v-col>
      <v-col>
        <v-btn v-if="showAddTenantInGrid" small color="info" style="float: right" class="my-4 mr-3" @click="addTenant">Create Tenant</v-btn>
      </v-col>
    </v-row>

    <div class="px-3 mb-5">
      <v-row>
        <v-col cols="4">
          <v-text-field
            v-model="filterByName"
            label="Search by name"
            dense
            @blur="getDataFromApi"
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

    <ManageTenantComponent
      v-if="addingTenant"
      v-model="addingTenant"
      @tenant-saved="getDataFromApi"
    />

  </v-card>

</template>

<script>

import ManageTenantComponent from '~/components/tenants/ManageTenant'
import Grid from "~/components/kendo/grid2/Grid"

export default {
  name: "TenantsGrid",
  props: ['value', 'showAddTenantInGrid'],
  components: {
    ManageTenantComponent,
    Grid
  },
  data: () => ({
    loading: false,
    addingTenant: false,
    filterByName: null,
    showGrid: false,
    gridData: [],
    gridColumns: [],
    gridSchema: {},
    gridGroup: null,
    gridAggregate: [],
    kColumnWidthDefault: "225px",
    kDefaultContains: {cell: {operator: 'contains'}},
  }),
  created () {
    this.getDataFromApi()
  },
  methods: {
    async getDataFromApi() {
      this.loading = true
      this.showGrid = false

      const response = await this.$axios.$get('tenants', {
        params: {
          filterByName: this.filterByName,
          defaultUser: true
        }
      })

      let tGridData = []
      for (let row of response) {
        tGridData.push({id: row.id, name: row.name, abbrv: row.abbrv, default_user_id: row.default_user_id, default_user_name: row.default_user_name, created_at: row.created_at, udpated_at: row.udpated_at})
      }
      this.gridData = tGridData

      this.gridColumns = []
      this.gridColumns.push({field: 'name', title: 'Name', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'abbrv', title: 'Abbreviation', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'created_at', title: 'Created Date', hidden: true, format: '{0:MM/dd/yyyy}', width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'updated_at', title: 'Last Updated Date', hidden: true, format: '{0:MM/dd/yyyy}', width: this.kColumnWidthDefault})
      // this.gridColumns.push({field: 'default_user_name', title: 'Default User', hidden: true})
      this.gridColumns.push({field: 'impersonate', title: 'Impersonate Default User', hidden: false, template: this.impersonateUser('default_user_id', 'default_user_name'), width: '150px'})
      this.gridColumns.push({field: 'edit', title: 'Manage', hidden: false, template: this.openData('id'), width: '100px'})
      this.gridSchema = {
        model: {
          id: 'id',
          fields: {
            name: {type: 'string'},
            abbrv: {type: 'string'},
            default_user_name: {type: 'string'},
            default_user_id: {type: 'integer'},
            created_at: {type: 'date'},
            updated_at: {type: 'date'}
          }
        }
      }
      this.gridAggregate = [
      ]

      this.refresh()
      this.loading = false
    },
    openData (fieldKey) {
      return `# if(typeof data.${fieldKey} != 'undefined') { # <a href="/tenants/#=data.${fieldKey}#">Manage</a> # } #`
      //this.$router.push(`/tenants/${dataItem.id}`)
    },
    clearFilters () {
      this.filterByName = null
      //this.getDataFromApi()
    },
    addTenant () {
      this.addingTenant = true
    },
    refresh () {
      this.showGrid = false
      this.$nextTick().then(() => {
        this.showGrid = true
      })
    },
    impersonateUser (fieldKey, defaultUserName) {
      // return `# console.log('kendo data object', data); if(typeof data.${fieldKey} != 'undefined' && data.${fieldKey} != null) { # <a href="/impersonate?id=#=data.${fieldKey}#">xxxx</a> # } #`
      return `# if(typeof data.${fieldKey} != 'undefined' && data.${fieldKey} != null) { # <a href="/impersonate?id=#=data.${fieldKey}#">#=data.${defaultUserName}#</a> # } #`
      // this.$router.push(`/impersonate?id=${this.user.id}`)
    },
  }
}
</script>

<style scoped>

</style>
