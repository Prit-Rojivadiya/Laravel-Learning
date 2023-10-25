<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Engine Manufacturers</h2>
        </v-col>
        <v-col>
          <v-btn small color="info" style="float: right" @click="addEngineManufacturer">Add Engine Manufacturer</v-btn>
        </v-col>
      </v-row>
    </div>

    <v-card>
      <v-card-title>
        Engine Manufacturers
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
            <span>Manage Engine Manufacturer</span>
          </v-tooltip>
        </template>

      </v-data-table>

      <ManageEngineManufacturerComponent
        v-if="addingEngineManufacturer"
        v-model="addingEngineManufacturer"
        @engine_manufacturer-saved="getDataFromApi"
      />

    </v-card>
  </div>
</template>

<script>

import ManageEngineManufacturerComponent from '~/components/engine_manufacturers/ManageEngineManufacturer'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    ManageEngineManufacturerComponent
  },
  data: () => ({
    loading: true,
    addingEngineManufacturer: false,
    filterByName: null,
    headers: [
      // { text: 'Engine Manufacturer ID', align: 'left', value: 'id' },
      { text: 'Name', align: 'left', value: 'name' },
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

      const response = await this.$axios.$get('engine_manufacturers', {
        params: {
          page: this.options.page,
          _sort: (options.sortBy) ? options.sortBy[0] : null,
          _sort_dir: (options.sortDesc && options.sortDesc[0] == true) ? 'desc' : 'asc',
          filterByName: this.filterByName,
        }
      })

      this.tableData = response.data
      this.totalItems = response.total

      this.loading = false
    },
    async openData (dataItem) {
      this.$router.push(`/engine_manufacturers/${dataItem.id}`)
    },
    clearFilters () {
      this.filterByName = null
      this.getDataFromApi()
    },
    addEngineManufacturer () {
      this.addingEngineManufacturer = true
    }
  }
}
</script>
