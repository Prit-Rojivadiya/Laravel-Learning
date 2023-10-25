<template>
  <v-card class="mt-15">
    <v-row>
      <v-col cols="12">
        <v-card-title>
          Meter Readings
        </v-card-title>
      </v-col>
      <v-col>
        <v-btn v-if="showAddMeterReadingInGrid" small color="info" style="float: right" class="my-4 mr-3" @click="addMeterReading">Add Meter Reading</v-btn>
      </v-col>
    </v-row>

    <div class="px-3 mb-5">
      <v-row>
        <v-col cols="4">
          <v-text-field
            v-model="filterByName"
            label="Search by source"
            dense
            @blur="getDataFromApi"
            v-on:keyup.enter="getDataFromApi"
          />
        </v-col>
        <v-col cols="4" v-if="!this.vehicle">
          <ModelNameSearchSelect
            v-model="filteredVehicle"
            dense
            :prefill="true"
            :search_label="'Search by Vehicle'"
            :api_url_base="'vehicles'"
          />
        </v-col>
        <v-col>
          <v-btn small color="primary" style="float: right" class="ml-5 mb-2" :loading="loading" @click="getDataFromApi">Search</v-btn>
          <v-btn small style="float: right;" class="ml-5 mb-2" :disabled="loading" @click="clearFilters">Clear</v-btn>
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
      <template v-slot:item.meter_reading_date="{ item, value }">
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
          <span>Manage Meter Reading</span>
        </v-tooltip>
      </template>

    </v-data-table>

    <ManageMeterReadingComponent
      v-if="addingMeterReading"
      v-model="addingMeterReading"
      :vehicle="vehicle"
      @meterReading-saved="getDataFromApi"
    />

  </v-card>

</template>

<script>

import ModelNameSearchSelect from '~/components/forms/ModelNameSearchSelect'
import ManageMeterReadingComponent from '~/components/meter_readings/ManageMeterReading'

export default {
  name: "MeterReadingsGrid",
  props: ['value', 'vehicle', 'showAddMeterReadingInGrid', 'showVehicleInGrid'],
  components: {
    ManageMeterReadingComponent,
    ModelNameSearchSelect
  },
  data: () => ({
    loading: true,
    addingMeterReading: false,
    filterByName: null,
    filteredVehicle: null,
    tableData:[],
    totalItems: 0,
    options: {page: 1},
  }),
  computed: {
    headers () {
      let headers = []
      //headers.push({ text: 'Meter Reading ID', align: 'left', value: 'id' })
      headers.push({ text: 'Meter Reading', align: 'left', value: 'meter_reading' })
      if (this.showVehicleInGrid) {
        headers.push({text: 'Vehicle', align: 'left', value: 'vehicle.vehicle_number'})
      }
      headers.push({ text: 'Source', align: 'left', value: 'source' })
      headers.push({ text: 'Date', align: 'left', value: 'meter_reading_date' })
      headers.push({ text: 'Actions', value: 'actions', sortable: false })
      return headers
    },
    parentVehicle() {
      if (this.vehicle) {
        return this.vehicle
      }
      else {
        return null
      }
    }
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.tableData = []

      if (options) {
        this.options = options
      } else {
        options = { sortBy: ['meter_reading'], sortDesc: [true] }
      }

      if (options.sortBy.length == 0) {
        options.sortBy = ['meter_reading']
      }
      if (options.sortDesc.length == 0) {
        options.sortDesc = [true]
      }

      if (this.vehicle && !this.filteredVehicle) {
        this.filteredVehicle = this.vehicle
      }

      const response = await this.$axios.$get('meter_readings', {
        params: {
          page: this.options.page,
          _sort: (options.sortBy) ? options.sortBy[0] : null,
          _sort_dir: (options.sortDesc && options.sortDesc[0] == true) ? 'desc' : 'asc',
          filterByName: this.filterByName,
          filterByVehicle: (this.filteredVehicle) ? this.filteredVehicle.id : null,
        }
      })

      this.tableData = response.data
      this.totalItems = response.total

      this.loading = false
    },
    openData (dataItem) {
      this.$router.push(`/meter_readings/${dataItem.id}`)
    },
    clearFilters () {
      this.filterByName = null
      this.filteredVehicle = null
      this.getDataFromApi()
    },
    addMeterReading () {
      this.addingMeterReading = true
    }
  }
}
</script>

<style scoped>

</style>
