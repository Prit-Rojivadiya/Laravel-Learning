<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Vehicle Types</h2>
        </v-col>
        <v-col>
          <v-btn small color="info" style="float: right" @click="addVehicleType">Add Vehicle Type</v-btn>
        </v-col>
      </v-row>
    </div>

    <v-card>
      <v-card-title>
        Vehicle Types
      </v-card-title>

      <div class="px-3 mb-5">
        <v-row>
          <v-col cols="4"  cols-xs="12" dense>
            <v-text-field
              v-model="filterByName"
              label="Search by name"
              dense
              @blur="getDataFromApi"
              v-on:keyup.enter="getDataFromApi"
            />
          </v-col>
          <v-col cols="4">
            <ModelNameSearchSelect
              v-model="filteredSystemVehicleType"
              dense
              :prefill="true"
              :search_label="'Search by Type'"
              :api_url_base="'system_vehicle_types'"
            />
          </v-col>

          <v-col>
            <v-btn small color="primary" style="float: right" :loading="loading" @click="getDataFromApi">Search</v-btn>
            <v-btn small style="float: right;" class="mr-5" :disabled="loading" @click="clearFilters">Clear</v-btn>
          </v-col>
        </v-row>
      </div>

      <v-data-table
        :headers="headers"
        :loading="loading"
        :items="tableData"
        item-key="id"
        :items-per-page="100"
        :footer-props="{ itemsPerPageOptions: [50, 100, 200, 500, -1] }"
        :server-items-length="totalItems"
        dense
        @update:options="getDataFromApi"
        @click:row="openData"
      >
        <template v-slot:item.created_at="{ item, value }">
          <template v-if="value">{{ value | formatDateMDY }}</template>
        </template>
        <template v-slot:item.updated_at="{ item, value }">
          {{ value | formatDateMDY }}
        </template>
        <template v-slot:item.actions="{ item }">
          <v-tooltip top>
            <template v-slot:activator="{ on, attrs }">
              <v-icon
                small
                class="mr-2"
                v-bind="attrs"
                v-on="on"
              >
                mdi-pencil
              </v-icon>
            </template>
            <span>Manage Vehicle Type</span>
          </v-tooltip>
        </template>

      </v-data-table>

      <ManageVehicleTypeComponent
        v-if="addingVehicleType"
        v-model="addingVehicleType"
        @vehicle_type-saved="getDataFromApi"
      />

    </v-card>
  </div>
</template>

<script>

import ModelNameSearchSelect from '~/components/forms/ModelNameSearchSelect'
import ManageVehicleTypeComponent from '~/components/vehicle_types/ManageVehicleType'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    ManageVehicleTypeComponent, ModelNameSearchSelect
  },
  data: () => ({
    loading: true,
    addingVehicleType: false,
    filterByName: null,
    filteredSystemVehicleType: null,
    headers: [
      // { text: 'Vehicle Type ID', align: 'left', value: 'id' },
      { text: 'Name', align: 'left', value: 'name' },
      { text: 'Type', align: 'left', value: 'system_vehicle_type_name' },
      { text: 'Desc', align: 'left', value: 'desc' },
      { text: 'Created Date', align: 'left', value: 'created_at' },
      { text: 'Updated Date', align: 'left', value: 'updated_at' },
      { text: 'Actions', value: 'actions', sortable: false }
    ],
    tableData:[],
    totalItems: 0,
    options: {page: 1},
  }),
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.tableData = []

      if (options) {
        this.options = options
      } else {
        options = { sortBy: null, sortDesc: null }
      }

      const response = await this.$axios.$get('vehicle_types', {
        params: {
          page: this.options.page,
          _sort: (options.sortBy) ? options.sortBy[0] : null,
          _sort_dir: (options.sortDesc && options.sortDesc[0] == true) ? 'desc' : 'asc',
          filterByName: this.filterByName,
          filterBySystemVehicleType: (this.filteredSystemVehicleType) ? this.filteredSystemVehicleType.id : null,
        }
      })

      this.tableData = response.data
      this.totalItems = response.total

      this.loading = false
    },
    async openData (dataItem) {
      this.$router.push(`/vehicle_types/${dataItem.id}`)
    },
    clearFilters () {
      this.filterByName = null
      this.filteredSystemVehicleType = null
      this.getDataFromApi()
    },
    addVehicleType () {
      this.addingVehicleType = true
    }
  }
}
</script>
