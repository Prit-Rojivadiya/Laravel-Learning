<template>
  <v-card class="mt-15">
    <v-row>
      <v-col cols="12">
        <v-card-title>
          Preventive Maintenance Items
        </v-card-title>
      </v-col>
      <v-col>
        <v-btn v-if="showAddPreventiveMaintenanceInGrid" small color="info" style="float: right" class="my-4 mr-3" @click="addPreventiveMaintenance">Add Preventive Maintenance Item</v-btn>
      </v-col>
    </v-row>

    <div class="px-3 mb-5">
<!--      <v-row>-->
<!--        <v-col cols="4">-->
<!--          <v-checkbox-->
<!--            v-model="includeMeterReading"-->
<!--            :label="`Include Latest Meter Reading`"-->
<!--          />-->
<!--        </v-col>-->
<!--      </v-row>-->
      <v-row>
        <v-col cols="4" v-if="showVehicleInGrid">
          <ModelNameSearchSelect
            v-model="filteredClient"
            dense
            :prefill="true"
            :search_label="'Account'"
            :api_url_base="'clients'"
            :includeAllSelection="true"
            :rules="[v => !!v || 'Please select an account']"
            @input="updateClientBranchFleet"
          />
        </v-col>
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
      </v-row>
      <v-row>
        <v-col cols="4">
          <StatusSearchSelect
            v-model="filteredStatus"
            dense
            :prefill="true"
            :search_label="'Search by Status'"
            :pmStatusList="pmStatusList"
          />
        </v-col>
        <v-col cols="4">
          <ModelNameSearchSelect
            v-model="filteredPMDueType"
            dense
            :prefill="true"
            :search_label="'Filter PM Due by'"
            :api_url_base="'system_p_m_due_types'"
            :includeAllSelection="true"
            :rules="[v => !!v || 'Please select a PM Type']"
            @input="updatePMDueTypeId"
          />
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="4">
          <v-checkbox
            style="margin-top: 0"
            v-model="groupByVehicle"
            :label="`Group By Vehicle`"
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
      :kFilter="gridFilter"
      :kSort="gridSort"
      :kGroup="kGroup"
      :kToolBar="['excel', 'pdf']"
      :kPageSize="100"
    />

    <ManagePreventiveMaintenanceComponent
      v-if="addingPreventiveMaintenance"
      v-model="addingPreventiveMaintenance"
      :vehicle="vehicle"
      :allowVehicleAssignment="false"
      @preventive_maintenance-saved="getDataFromApi"
    />

  </v-card>

</template>

<script>

import ModelNameSearchSelect from '~/components/forms/ModelNameSearchSelect'
import StatusSearchSelect from '~/components/forms/StatusSearchSelect'
import ManagePreventiveMaintenanceComponent from '~/components/preventive_maintenances/ManagePreventiveMaintenance'
import VehicleNumberSearchSelect from "~/components/vehicles/VehicleNumberSearchSelect";
import Grid from "~/components/kendo/grid2/Grid"

export default {
  name: "PreventiveMaintenancesGrid",
  props: ['value', 'vehicle', 'showVehicleInGrid', 'showAddPreventiveMaintenanceInGrid', 'allowVehicleAssignment'],
  components: {
    VehicleNumberSearchSelect,
    ManagePreventiveMaintenanceComponent,
    ModelNameSearchSelect,
    StatusSearchSelect,
    Grid
  },
  data: () => ({
    loading: false,
    addingPreventiveMaintenance: false,
    showClientBranchFleet: true,
    filteredClient: {id: 'all', name: "All"},
    filteredClientId: null,
    filteredBranch: {id: 'all', name: "All"},
    filteredBranchId: null,
    filteredFleet: {id: 'all', name: "All"},
    filteredFleetId: null,
    filteredVehicle: null,
    filteredStatus: {id: "due-soon", name: "Due Soon"},
    filteredPMDueType: {id: "all", name: "All"},
    filteredPMDueTypeId: null,
    includeMeterReading: true,
    groupByVehicle: true,
    pmStatusList: [
      {id: "due-soon", name: "Due Soon"},
      {id: "overdue", name: "Overdue"},
      {id: "open", name: "Open"},
      {id: "completed", name: "Completed"},
      {id: "all", name: "All"},
    ],
    showGrid: false,
    gridData: [],
    gridColumns: [],
    gridSchema: {},
    gridFilter: {},
    gridSort: {},
    kGroup: null,
    kColumnWidthDefault: "225px",
    kDefaultContains: {cell: {operator: 'contains'}},
  }),
  created() {
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

      const response = await this.$axios.$get('preventive_maintenances', {
        params: {
          filterByClient: (this.filteredClientId && this.filteredClientId != 'all') ? this.filteredClientId : null,
          filterByBranch: (this.filteredBranchId && this.filteredBranchId != 'all') ? this.filteredBranchId : null,
          filterByFleet: (this.filteredFleetId && this.filteredFleetId != 'all') ? this.filteredFleetId : null,
          filterByVehicle: (this.filteredVehicle) ? this.filteredVehicle.id : null,
          filterByStatus: (this.filteredStatus) ? this.filteredStatus.id : null,
          filterByPMDueType: (this.filteredPMDueTypeId && this.filteredPMDueTypeId != 'all') ? this.filteredPMDueTypeId : null,
          includeMeterReading: this.includeMeterReading,
        }
      })

      this.gridData = response
      this.gridColumns = []
      this.gridColumns.push({field: 'name', title: 'Name', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('id', 'name', '/preventive_maintenances', 'tz-grid-hlink') })
      if (this.showVehicleInGrid) {
        let hidden = false
        hidden = (this.filteredClientId && this.filteredClientId != 'all')
        this.gridColumns.push({field: 'client_name', title: 'Account', hidden: hidden, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('client_id', 'client_name', '/clients', 'tz-grid-hlink') })
        hidden = (this.filteredBranchId && this.filteredBranchId != 'all')
        this.gridColumns.push({field: 'branch_name', title: 'Branch', hidden: hidden, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('branch_id', 'branch_name', '/branches', 'tz-grid-hlink') })
        hidden = (this.filteredFleetId && this.filteredFleetId != 'all')
        this.gridColumns.push({field: 'fleet_name', title: 'Fleet', hidden: hidden, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('fleet_id', 'fleet_name', '/fleets', 'tz-grid-hlink') })
        this.gridColumns.push({field: 'vehicle_number', title: 'Vehicle Unit Number', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('vehicle_id', 'vehicle_number', '/vehicles', 'tz-grid-hlink') })
      }
      this.gridColumns.push({field: 'due_date', title: 'Due Date', hidden: false, format: '{0:MM/dd/yyyy}', width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'due_at_meter', title: 'Due At Meter', hidden: false, format: '{0:n0}', width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'remainingDays', title: 'Days Remaining', hidden: false, format: '{0:n0}', width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'remainingMeters', title: 'Distance Remaining', hidden: false, format: '{0:n0}', width: this.kColumnWidthDefault})
      if (this.includeMeterReading) {
        this.gridColumns.push({field: 'latestMeterReading', title: 'Latest Meter Reading', hidden: false, format: '{0:n0}', width: this.kColumnWidthDefault})
      }
      this.gridColumns.push({field: 'overdue', title: 'Is Overdue', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'hasRepairOrder', title: 'Has Repair Order', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'ro_number', title: 'Repair Order #', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('ro_id', 'ro_number', '/repair_orders', 'tz-grid-hlink') })
      this.gridColumns.push({field: 'created_at', title: 'Created Date', hidden: true, format: '{0:MM/dd/yyyy}', width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'completed_date', title: 'Completed Date', hidden: false, format: '{0:MM/dd/yyyy}', width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'updated_at', title: 'Last Updated Date', hidden: true, format: '{0:MM/dd/yyyy}', width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'pmDueType', title: 'PM Due Type', hidden: true, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
      this.gridColumns.push({field: 'edit', title: 'Manage', hidden: false, width: '100px', template: this.openData('id', null, '/preventive_maintenances', null, 'Manage') })
      this.gridSchema = {
        model: {
          id: "id",
          fields: {
            name: {type: 'string'},
            id: {type: 'number'},
            client_name: {type: 'string'},
            branch_name: {type: 'string'},
            fleet_name: {type: 'string'},
            vehicle_number: {type: 'string', from: "vehicle.vehicle_number"},
            hasRepairOrder: {type: 'boolean'},
            due_date: {type: 'date'},
            due_at_meter: {type: 'number'},
            remainingMeters: {type: 'number'},
            remainingDays: {type: 'number'},
            overdue: {type: 'boolean'},
            ro_id: {type: 'number', from: "repair_order_id"},
            ro_number: {type: 'string', from: "repair_order.ro_number"},
            pmDueType: {type: 'string', from: "system_p_m_due_type.name"},
            created_at: {type: 'date'},
            completed_date: {type: 'date'},
            updated_at: {type: 'date'}
          }
        }
      }

      // hide columns based on search filter
      for (let gridColumn of this.gridColumns) {
        if (this.filteredPMDueType.name === 'Days') {
          if (gridColumn.field === 'due_date' || gridColumn.field === 'remainingDays') {
            gridColumn.hidden = false
          }
          if (gridColumn.field === 'due_at_meter' || gridColumn.field === 'remainingMeters') {
            gridColumn.hidden = true
          }
        }
        else if (this.filteredPMDueType.name === 'Interval') {
          if (gridColumn.field === 'due_date' || gridColumn.field === 'remainingDays') {
            gridColumn.hidden = true
          }
          if (gridColumn.field === 'due_at_meter' || gridColumn.field === 'remainingMeters') {
            gridColumn.hidden = false
          }
        }
      }

      // default filtering
      // if (this.filteredStatus.id == 'due-soon') {
      //   let now = new Date()
      //   let nowPlus30 = new Date(now.setMonth(now.getMonth() + 1));
      //   now = new Date()
      //   let nowMinus30 = new Date(now.setMonth(now.getMonth() - 1));
      //   this.gridFilter = [
      //     {field: "due_date", operator: "lte", value: nowPlus30},
      //     {field: "due_date", operator: "gte", value: nowMinus30}
      //   ]
      // }
      // else {
      //   this.gridFilter = null
      // }

      // default grouping
      if (this.groupByVehicle) {
        this.kGroup = {
          field: 'vehicle_number'
        }
      }
      else {
        this.kGroup = null
      }

      // default sorting
      switch (this.filteredStatus.id) {
        case 'due-soon':
          if (this.filteredPMDueType.name === 'Interval') {
            this.gridSort = { field: "due_at_meter", dir: "asc" }
          }
          else {
            this.gridSort = { field: "due_date", dir: "asc" }
          }
          break;
        case 'overdue':
          if (this.filteredPMDueType.name === 'Interval') {
            this.gridSort = { field: "due_at_meter", dir: "asc" }
          }
          else {
            this.gridSort = { field: "due_date", dir: "asc" }
          }
          break;
        case 'open':
          if (this.filteredPMDueType.name === 'Interval') {
            this.gridSort = { field: "due_at_meter", dir: "asc" }
          }
          else {
            this.gridSort = { field: "due_date", dir: "asc" }
          }
          break;
        case 'completed':
          this.gridSort = { field: "completed_date", dir: "desc" }
          break;
        case 'all':
          this.gridSort = null
          break;
        default:
          this.gridSort = null
      }

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
      // this.$router.push(`/preventive_maintenances/${dataItem.id}`)
    },
    clearFilters () {
      this.filteredVehicle = null
      this.filteredStatus = 'all'
      this.filteredClient = { id: 'all', name: 'All' }
      this.filteredClientId = null
      this.filteredBranch = { id: 'all', name: 'All' }
      this.filteredBranchId = null
      this.filteredFleet = { id: 'all', name: 'All' }
      this.filteredFleetId = null
      this.filteredPMDueType = { id: 'all', name: 'All' }
      this.filteredPMDueTypeId = null
      this.groupByVehicle = true
      // this.getDataFromApi()
    },
    addPreventiveMaintenance () {
      this.addingPreventiveMaintenance = true
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
        if (this.filteredClient.id != 'all') {
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
    updatePMDueTypeId () {
      if (this.filteredPMDueType) {
        if(this.filteredPMDueType.id != 'all') {
          this.filteredPMDueTypeId = this.filteredPMDueType.id
        }
        else {
          this.filteredPMDueTypeId = null
        }
      }
    }
  }
}
</script>

<style scoped>

</style>
