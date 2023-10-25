<template>
  <v-card class="mt-15">
    <v-row>
      <v-col cols="12">
        <v-card-title>
          Repair Orders
        </v-card-title>
      </v-col>
      <v-col>
        <v-btn v-if="showAddRepairOrderInGrid" small color="info" style="float: right" class="my-4 mr-3" @click="addRepairOrder">Add Repair Order</v-btn>
      </v-col>
    </v-row>

    <div class="px-3 mb-5">
      <v-row>
        <v-col cols="4">
          <v-text-field
            v-model="filterByRONumber"
            label="Search RO #"
            dense
            @blur="getDataFromApi"
          />
        </v-col>
        <v-col cols="4">
          <v-text-field
            v-model="filterByInvoiceNumber"
            label="Search Invoice #"
            dense
            @blur="getDataFromApi"
          />
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="4">
          <ModelNameSearchSelect
            v-model="filteredStatus"
            dense
            :prefill="true"
            :search_label="'Search by Status'"
            :api_url_base="'repair_order_statuses'"
          />
        </v-col>
        <v-col cols="4" v-if="showVehicleInGrid">
          <ModelNameSearchSelect
            v-model="filteredClient"
            dense
            :prefill="true"
            :search_label="'Account'"
            :api_url_base="'clients'"
            :rules="[v => !!v || 'Please select a client']"
            @input="updateClientBranchFleet"
          />
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="4" v-if="showVehicleInGrid">
          <ModelNameSearchSelect
            v-if="showClientBranchFleet"
            v-model="filteredBranch"
            dense
            :prefill="true"
            :search_label="'Branch'"
            :api_url_base="'branches'"
            :includeAllSelection="true"
            :additionalParams="{filterByClient: this.filteredClientId}"
            :rules="[v => !!v || 'Please select a branch']"
            @input="updateClientBranchFleet"
          />
        </v-col>
        <v-col cols="4" v-if="showVehicleInGrid">
          <ModelNameSearchSelect
            v-if="showClientBranchFleet"
            v-model="filteredFleet"
            dense
            :prefill="true"
            :search_label="'Fleet'"
            :api_url_base="'fleets'"
            :includeAllSelection="true"
            :additionalParams="{filterByBranch: this.filteredBranchId}"
            :rules="[v => !!v || 'Please select a fleet']"
            @input="updateClientBranchFleet"
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


    <ManageRepairOrderComponent
      v-if="addingRepairOrder"
      v-model="addingRepairOrder"
      :vehicle="vehicle"
      :allowVehicleAssignment="false"
      @repair_order-saved="getDataFromApi"
    />

  </v-card>

</template>

<script>

import ModelNameSearchSelect from '~/components/forms/ModelNameSearchSelect'
import StatusSearchSelect from '~/components/forms/StatusSearchSelect'
import ManageRepairOrderComponent from '~/components/repair_orders/ManageRepairOrder'
import VehicleNumberSearchSelect from "~/components/vehicles/VehicleNumberSearchSelect";
import Grid from "~/components/kendo/grid2/Grid"

export default {
  name: "RepairOrdersGrid",
  props: ['value', 'vehicle', 'showVehicleInGrid', 'showAddRepairOrderInGrid', 'allowVehicleAssignment'],
  components: {
    VehicleNumberSearchSelect,
    ManageRepairOrderComponent,
    ModelNameSearchSelect,
    StatusSearchSelect,
    Grid
  },
  data: () => ({
    loading: false,
    addingRepairOrder: false,
    filterByRONumber: null,
    filterByInvoiceNumber: null,
    showClientBranchFleet: true,
    filteredClient: null,
    filteredClientId: null,
    filteredBranch: {id: 'all', name: "All"},
    filteredBranchId: null,
    filteredFleet: {id: 'all', name: "All"},
    filteredFleetId: null,
    filteredVehicle: null,
    filteredStatus: null,
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
    if(!this.showVehicleInGrid) {
      this.getDataFromApi()
    }
  },
  methods: {
    async getDataFromApi() {
      this.loading = true
      this.showGrid = false

      if (this.vehicle && !this.filteredVehicle) {
        this.filteredVehicle = this.vehicle
      }

      const response = await this.$axios.$get('repair_orders', {
        params: {
          filterByRONumber: this.filterByRONumber,
          filterByInvoiceNumber: this.filterByInvoiceNumber,
          filterByClient: (this.filteredClientId && this.filteredClientId != 'all') ? this.filteredClientId : null,
          filterByBranch: (this.filteredBranchId && this.filteredBranchId != 'all') ? this.filteredBranchId : null,
          filterByFleet: (this.filteredFleetId && this.filteredFleetId != 'all') ? this.filteredFleetId : null,
          filterByVehicle: (this.filteredVehicle) ? this.filteredVehicle.id : null,
          filterByStatus: (this.filteredStatus) ? this.filteredStatus.id : null,
        }
      })

      this.gridData = response
      this.gridColumns = []

      this.gridColumns.push({field: 'ro_number', title: 'RO #', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('id', 'ro_number', '/repair_orders', 'tz-grid-hlink') })
      this.gridColumns.push({field: 'vehicle_number', title: 'Vehicle Unit Number', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('vehicle_id', 'vehicle_number', '/vehicles', 'tz-grid-hlink') })
      this.gridColumns.push({field: 'desc', title: 'Description', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'invoice_number', title: 'Invoice #', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'total_price', title: 'Total Price', hidden: false, format: "{0:c}", width: this.kColumnWidthDefault, aggregates: ['sum'], groupFooterTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#", footerTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#"})
      this.gridColumns.push({field: 'vendor_name', title: 'Vendor', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, aggregates: ["count"], groupHeaderTemplate: "#=data.value# (#= count#)", template: this.openData('vendor_id', 'vendor_name', '/vendors', 'tz-grid-hlink') })
      this.gridColumns.push({field: 'repair_order_status_name', title: 'Status', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
      if (this.showVehicleInGrid) {
        let hidden = false
        hidden = (this.filteredClientId && this.filteredClientId != 'all')
        this.gridColumns.push({field: 'client_name', title: 'Account', hidden: hidden, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('client_id', 'client_name', '/clients', 'tz-grid-hlink') })
        hidden = (this.filteredBranchId && this.filteredBranchId != 'all')
        this.gridColumns.push({field: 'branch_name', title: 'Branch', hidden: hidden, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('branch_id', 'branch_name', '/branches', 'tz-grid-hlink') })
        hidden = (this.filteredFleetId && this.filteredFleetId != 'all')
        this.gridColumns.push({field: 'fleet_name', title: 'Fleet', hidden: hidden, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('fleet_id', 'fleet_name', '/fleets', 'tz-grid-hlink') })
      }
      this.gridColumns.push({field: 'created_at', title: 'Created Date', hidden: true, format: '{0:MM/dd/yyyy}', width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'completed_date', title: 'Completed Date', hidden: false, format: '{0:MM/dd/yyyy}', width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'updated_at', title: 'Last Updated Date', hidden: true, format: '{0:MM/dd/yyyy}', width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'edit', title: 'Manage', hidden: false, width: '100px', template: this.openData('id', null, '/repair_orders', null, 'Manage') })
      this.gridSchema = {
        model: {
          id: "id",
          fields: {
            desc: {type: 'string'},
            ro_number: {type: 'string'},
            invoice_number: {type: 'string'},
            client_name: {type: 'string'},
            branch_name: {type: 'string'},
            fleet_name: {type: 'string'},
            vehicle_number: {type: 'string', from: "vehicle.vehicle_number"},
            vendor_name: {type: 'string'},
            repair_order_status_name: {type: 'string', from: "repair_order_status.name"},
            total_price: {type: 'number'},
            created_at: {type: 'date'},
            completed_date: {type: 'date'},
            updated_at: {type: 'date'}
          }
        }
      }
      // this.gridGroup = {
      //   field: 'vendor_name', aggregates: [
      //     {field: 'vendor_name', aggregate: "count"},
      //     {field: 'total_price', aggregate: "sum"}
      //   ]
      // }
      this.gridAggregate = [
        {field: 'vendor_name', aggregate: "count"},
        {field: 'total_price', aggregate: "sum"}
      ]

      this.refresh()
      this.loading = false
    },
    openData (fieldKey, fieldKeyForLabel, link, sclass, staticLabel) {
      if (!sclass) {
        sclass = ''
      }
      if (staticLabel) {
        return `# if(typeof data.${fieldKey} != 'undefined') { # <a class="${sclass}" href="${link}/#=data.${fieldKey}#">${staticLabel}</a> # } #`
      }
      else {
        return `# if(typeof data.${fieldKey} != 'undefined' && data.${fieldKey} != null) { # <a class="${sclass}" href="${link}/#=data.${fieldKey}#">#=data.${fieldKeyForLabel}#</a> # } #`
      }
      //this.$router.push(`/repair_orders/${dataItem.id}`)
    },
    clearFilters () {
      this.filterByRONumber = null
      this.filterByInvoiceNumber = null
      this.filteredVehicle = null
      this.filteredStatus = null
      this.filteredClient=null
      this.filteredClientId=null
      this.filteredBranch={id: 'all', name: "All"}
      this.filteredBranchId=null
      this.filteredFleet={id: 'all', name: "All"}
      this.filteredFleetId=null
      //this.getDataFromApi()
    },
    addRepairOrder () {
      this.addingRepairOrder = true
    },
    refresh () {
      this.showGrid = false
      this.$nextTick().then(() => {
        this.showGrid = true
      })
    },
    updateClientBranchFleet () {
      this.showClientBranchFleet = false
      if (this.filteredClient) {
        if(this.filteredClient.id != 'all') {
          this.filteredClientId = this.filteredClient.id
        }
        else {
          this.filteredClientId = null
        }
      }
      if (this.filteredBranch) {
        if(this.filteredBranch.id != 'all') {
          this.filteredBranchId = this.filteredBranch.id
        }
        else {
          this.filteredBranchId = null
        }
      }
      if (this.filteredFleet) {
        if(this.filteredFleet.id != 'all') {
          this.filteredFleetId = this.filteredFleet.id
        }
        else {
          this.filteredFleetId = null
        }
      }
      this.$nextTick().then(() => {
        this.showClientBranchFleet = true
      });
    },
  }
}
</script>

<style scoped>

</style>
