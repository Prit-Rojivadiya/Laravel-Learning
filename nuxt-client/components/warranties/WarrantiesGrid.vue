<template>
  <v-card class="mt-15">
    <v-row>
      <v-col cols="12">
        <v-card-title>
          Warranties
        </v-card-title>
      </v-col>
      <v-col>
        <v-btn v-if="showAddWarrantyInGrid" small color="info" style="float: right" class="my-4 mr-3" @click="addWarranty">Add Warranty</v-btn>
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
          <span>Manage Warranty</span>
        </v-tooltip>
      </template>

    </v-data-table>

    <ManageWarrantyComponent
      v-if="addingWarranty"
      v-model="addingWarranty"
      :vehicle="vehicle"
      @warranty-saved="getDataFromApi"
    />

  </v-card>

</template>

<script>

import ModelNameSearchSelect from '~/components/forms/ModelNameSearchSelect'
import ManageWarrantyComponent from '~/components/warranties/ManageWarranty'

export default {
  name: "WarrantiesGrid",
  props: ['value', 'vehicle', 'showAddWarrantyInGrid', 'showVehicleInGrid'],
  components: {
    ManageWarrantyComponent,
    ModelNameSearchSelect
  },
  data: () => ({
    loading: true,
    addingWarranty: false,
    filterByName: null,
    filteredVehicle: null,
    tableData:[],
    totalItems: 0,
    options: {page: 1},
  }),
  computed: {
    headers () {
      let headers = []
      //headers.push({ text: 'Warranty ID', align: 'left', value: 'id' })
      headers.push({ text: 'Name', align: 'left', value: 'name' })
      if (this.showVehicleInGrid) {
        headers.push({text: 'Vehicle', align: 'left', value: 'vehicle.vehicle_number'})
      }
      headers.push({ text: 'Created Date', align: 'left', value: 'created_at' })
      //headers.push({ text: 'Updated Date', align: 'left', value: 'updated_at' })
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
        options = { sortBy: null, sortDesc: null }
      }

      if (this.vehicle && !this.filteredVehicle) {
        this.filteredVehicle = this.vehicle
      }


      const response = await this.$axios.$get('warranties', {
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
      this.$router.push(`/warranties/${dataItem.id}`)
    },
    clearFilters () {
      this.filterByName = null
      this.filteredVehicle = null
      this.getDataFromApi()
    },
    addWarranty () {
      this.addingWarranty = true
    }
  }
}
</script>

<style scoped>

</style>
