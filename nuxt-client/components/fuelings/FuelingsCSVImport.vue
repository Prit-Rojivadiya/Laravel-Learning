<template>
  <v-card class="mt-5">
    <v-row>
      <v-col cols="10">
        <v-card-title>
          Import Fuel Events from CSV file
        </v-card-title>
        <div class="ml-5">
          <div class="mb-1">To batch import fuel transactions, please follow these steps: <br/></div>
          <div class="mb-1">1. Select Client and the .csv file to be imported. Check if the first row contains headers. <br/></div>
          <div class="mb-1">2. Click "Preview Import" and fill in all of the columns to be imported. When each column has been mapped, a preview of the data to be imported will appear. <br/></div>
          <div class="mb-1">3. Click "Run Import" to complete the upload.<br/></div>
          <div class="mb-1">4. Review any rows that failed to be processed, fix the issues, and re-import the file.<br/></div>
        </div>
      </v-col>
    </v-row>

    <div class="ml-5 mb-2">
      <v-row>
        <v-col cols="4">
          <ModelNameSearchSelect
            v-model="filteredClient"
            dense
            :prefill="true"
            :search_label="'Account'"
            :api_url_base="'clients'"
          />
        </v-col>
        <v-col cols="2" v-if="csvData">
          <v-btn small style="float: right;" class="mr-5 mb-2" :disabled="loading" @click="clearUpload">Reset</v-btn>
        </v-col>
      </v-row>

      <v-row>
        <v-col cols="8">
          <CSVImport
            v-if="showCSVImport"
            :mapFields="csvMapFields"
            @preview-csv="previewCSV"
            @import-csv="importCSV"
          />
        </v-col>
      </v-row>
    </div>

    <v-alert v-if="results.length">
      Fueling Import Completed <br />
      <template v-for="result in results">
        {{ result }}<br />
      </template>
    </v-alert>

    <v-alert v-if="errors.length" class="error">
      <template v-for="error in errors">
        {{ error.msg }}<br />
      </template>
      Please try again
    </v-alert>

    <v-data-table class="mt-5 mb-2"
      v-if="showPreviewData"
      :headers="headers"
      :loading="loading"
      :items="tableData"
      item-key="id"
      :items-per-page="100"
      :footer-props="{ itemsPerPageOptions: [50, 100, 200, 500, -1] }"
      :server-items-length="totalItems"
      dense
    >
      <template v-slot:item.fueling_date="{ item, value }">
        {{ value | formatDateMDY }}
      </template>
      <template v-slot:item.total_price="{ item, value }">
        {{ value | formatMoney }}
      </template>
      <template v-slot:item.total_units="{ item, value }">
        {{ value | formatMoneyNoSign }}
      </template>
    </v-data-table>

    <v-row v-if="csvData">
      <v-col cols="6" >
        <v-btn small color="primary" style="float: left" class="ml-5 mb-2" :loading="loading" @click="importCSV(csvData)">Run Import</v-btn>
      </v-col>
      <v-col cols="6" >
        <v-btn small style="float: right;" class="mr-5 mb-2" :disabled="loading" @click="clearUpload">Reset</v-btn>
      </v-col>
    </v-row>

  </v-card>



</template>

<script>

import CSVImport from '~/components/file_uploads/CSVImport'
import ModelNameSearchSelect from '~/components/forms/ModelNameSearchSelect'

export default {
  name: "FuelingsCSVImport",
  props: [],
  components: {
    CSVImport,
    ModelNameSearchSelect
  },
  data: () => ({
    showCSVImport: true,
    showPreviewData: false,
    loading: false,
    tableData:[],
    totalItems: 0,
    filteredClient: null,
    csvMapFields : [
      'Vehicle Number',
      'Fueling Date',
      'Meter',
      'Gallons',
      'Price Per Gallon',
      'Fuel Type',
      'Vendor',
      'State',
      'Country',
    ],
    csvData: null,
    errors: [],
    results: [],
  }),
  computed: {
    headers () {
      let headers = []
      headers.push({text: 'Vehicle Number', align: 'left', value: 'Vehicle Number'})
      headers.push({ text: 'Fueling Date', align: 'left', value: 'Fueling Date' })
      headers.push({ text: 'Meter', align: 'left', value: 'Meter' })
      headers.push({ text: 'Gallons', align: 'left', value: 'Gallons' })
      headers.push({ text: 'Price Per Gallon', align: 'left', value: 'Price Per Gallon' })
      headers.push({ text: 'Fuel Type', align: 'left', value: 'Fuel Type' })
      headers.push({ text: 'Vendor', align: 'left', value: 'Vendor' })
      headers.push({ text: 'State', align: 'left', value: 'State' })
      headers.push({ text: 'Country', align: 'left', value: 'Country' })
      return headers
    },
  },
  methods: {
    previewCSV(csvData) {
      this.csvData = csvData
      this.showPreviewData = true
      this.loading = true
      this.results = []
      this.tableData = []
      this.tableData = csvData
      this.totalItems = csvData.length
      this.loading = false
    },
    async importCSV(csvData) {
      this.loading = true
      this.csvData = csvData
      try {
        console.log("DEBUG: this.filteredClient", this.filteredClient);
        const response = await this.$axios.$post(`/fuelings/import`,
          {
            fuelings: this.csvData,
            filterByClient: (this.filteredClient) ? this.filteredClient.id : null,
          }
        )
        this.results = []
        if (response.total != null) {
          this.results.push(`Total fuel events processed: ${response.total}`)
        }
        if (response.imported != null) {
          this.results.push(`Total successfully imported: ${response.imported}`)
        }
        if (response.failed != null) {
          this.results.push(`Total failed: ${Object.keys(response.failed).length}`)
          for (let key in response.failed) {
            this.results.push(response.failed[key])
          }
        }
        this.$emit('import-fuelings-completed', response)
      } catch (e) {
        console.log("my error", e.message)
        if (e.response && e.response.status == 422) {
          this.errors = e.response.parsedErrors
        }
        else {
          this.errors.push ({msg: e})
        }
      }
      this.loading = false
      this.clearUpload()
    },
    clearUpload () {
      this.csvData = null
      this.showPreviewData = false
      this.showCSVImport = false
      this.tableData = []
      this.totalItems = 0
      this.errors = []
      this.$nextTick().then(() => {
        // Add the component back in
        this.showCSVImport = true
      });

    },
  }
}
</script>

<style scoped>

</style>
