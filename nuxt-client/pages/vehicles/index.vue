<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Vehicles Dashboard</h2>
        </v-col>
        <v-col>
          <v-btn small color="primary" style="float: right" class="ml-5 mb-2" :loading="loading" @click="runReport">Run Report</v-btn>
          <v-btn small style="float: right;" class="ml-5 mb-2" :disabled="loading" @click="clearFilters">Clear Filters</v-btn>
          <v-btn small color="info" style="float: right" class="ml-5 mb-2" @click="addVehicle">Add Vehicle</v-btn>
          <!--          <v-btn small color="info" style="float: right" class="ml-5 mb-2" @click="importFromFile">Import from File</v-btn>-->
        </v-col>
      </v-row>
      <v-row>
        <v-col>
        </v-col>
      </v-row>
    </div>
    <div>
      <v-form ref="form" v-model="formValid">
        <v-row>
          <v-col cols="4">
            <ModelNameSearchSelect
              v-model="filteredClient"
              dense
              :prefill="true"
              :search_label="'Account'"
              :api_url_base="'clients'"
              :includeAllSelection="true"
              :rules="[v => !!v || 'Please select a client']"
              @input="updateClientBranchFleet"
            />
          </v-col>
          <v-col cols="4">
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
          <v-col cols="4">
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
            <v-text-field
              v-model="filterByName"
              label="Search by vehicle unit number"
              dense
              v-on:keyup.enter="runReport"
            />
          </v-col>
        </v-row>
      </v-form>
    </div>

    <FileImportComponent
      v-if="importingFile"
      v-model="importingFile"
      :file_import_resource="file_import_resource"
      @file-imported="refresh"
    />

    <VehiclesGrid2Component
      v-if="showGrid"
      :filteredClientId="filteredClientId"
      :filteredBranchId="filteredBranchId"
      :filteredFleetId="filteredFleetId"
      :filteredByName="filterByName"
      @grid-data-loaded="gridDataLoaded"
    />

    <ManageVehicleComponent
      v-if="addingVehicle"
      v-model="addingVehicle"
      :fleet="fleet"
      @vehicle-saved="refresh"
    />

  </div>
</template>

<script>

import VehiclesGrid2Component from '~/components/vehicles/VehiclesGrid2'
import ManageVehicleComponent from '~/components/vehicles/ManageVehicle'
import FileImportComponent from '~/components/forms/FileUpload'
import ModelNameSearchSelect from '~/components/forms/ModelNameSearchSelect'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    VehiclesGrid2Component,
    ManageVehicleComponent,
    FileImportComponent,
    ModelNameSearchSelect
  },
  data: () => ({
    loading: false,
    addingVehicle: false,
    showGrid: false,
    showAddVehicleInGrid: false,
    importingFile: false,
    file_import_resource: "vehicle",
    fleet: null,
    showClientBranchFleet: true,
    filterByName: null,
    filteredClient: { id: 'all', name: "All" },
    filteredClientId: null,
    filteredBranch: { id: 'all', name: "All" },
    filteredBranchId: null,
    filteredFleet: { id: 'all', name: "All" },
    filteredFleetId: null,
    formValid: false,
    gridData: [],
  }),
  methods: {
    async runReport(options) {
      this.loading = true
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.loading = false
        return
      }
      this.showGrid = false
      this.$nextTick().then(() => {
        this.showGrid = true
      })
    },
    addVehicle () {
      this.addingVehicle = true
    },
    importFromFile() {
      this.importingFile = true
    },
    refresh () {
      this.runReport()
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
      })
    },
    gridDataLoaded (gridData, monthlyColumns) {
      this.loading = false
    },
    clearFilters () {
      this.filterByName = null
      this.filteredClient = { id: 'all', name: "All" }
      this.filteredClientId= null
      this.filteredBranch = { id: 'all', name: "All" }
      this.filteredBranchId = null
      this.filteredFleet = { id: 'all', name: "All" }
      this.filteredFleetId = null
    }
  }
}
</script>
