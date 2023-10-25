<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Invoice Summary Report</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="primary" style="float: right" class="ml-5 mb-2" :loading="loading" @click="getDataFromApi">Run Report</v-btn>
          <v-btn small style="float: right;" class="ml-5 mb-2" :disabled="loading" @click="clearFilters">Clear Filters</v-btn>
        </v-col>
      </v-row>
      <v-form ref="form" v-model="formValid">
        <v-row>
          <v-col cols="3">
            <div class="flex-item tz-form-item">
              <DatePicker
                v-if="showDates"
                v-model="startDate"
                label="Start Date"
                required
                :rules="[v => !!v || 'Please select the report range start date']"
              />
            </div>
          </v-col>
          <v-col cols="3">
            <div class="flex-item tz-form-item">
              <DatePicker
                v-if="showDates"
                v-model="endDate"
                label="End Date"
                required
                :rules="[v => !!v || 'Please select the report range end date']"
              />
            </div>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="3">
            <ModelNameSearchSelect
              v-model="filteredClient"
              dense
              :prefill="true"
              :search_label="'Account'"
              :api_url_base="'clients'"
            />
          </v-col>
        </v-row>
      </v-form>
    </div>

    <span v-if="loading">Preparing report, this make take a few minutes...</span>
    <v-progress-circular
      v-if="loading"
      indeterminate
      color="primary"
      class="ml-5"
    ></v-progress-circular>

    <KGrid
      class="compact-grid"
      v-if="gridDataLoaded"
      :height="700"
      :kGridData="gridData"
      :kSchema="gridSchema"
      :kColumns="kGridColumns"
      :kGroup="kGroup"
      :kAggregate="kGridAggregates"
      :kToolBar="['excel', 'pdf']"
      :kPageSize="100"
      :kExportFileName="kExportFileName"
      @force_refresh_grid="refreshGrid"
    />

  </div>
</template>

<script>

import DatePicker from '~/components/forms/DatePicker'
import ModelNameSearchSelect from '~/components/forms/ModelNameSearchSelect'
import KGrid from "~/components/kendo/grid2/Grid"

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    ModelNameSearchSelect,
    DatePicker,
    KGrid,
  },
  data: () => ({
    loading: true,
    gridDataLoaded: false,
    showDates: true,
    formValid: false,
    startDate: null,
    endDate: null,
    filteredClient: null,
    gridData: [],
    kGridColumns: [],
    kGroup: [
      {
        field: 'vendor_name',
        aggregates: [
          {field: 'total_price', aggregate: 'sum'},
          {field: 'vendor_name', aggregate: 'count'}
        ]
      }
    ],
    kColumnWidthDefault: "180px",
    kGridAggregates: [],
    kExportFileName: null,
    kDefaultContains: {cell: {operator: 'contains'}},
    dynamicColumnsAdded: false,

  }),
  async created () {
    this.loading = false
    let now = new Date()
    const offset = now.getTimezoneOffset()
    let tStart = new Date()
    let tEnd = new Date()
    tStart.setDate(tStart.getDate() - 7);
    this.startDate = (new Date(tStart.getTime() - (offset*60*1000))).toISOString().split('T')[0] //'2022-02-23T19:21:21.469Z'
    this.endDate = (new Date(tEnd.getTime() - (offset*60*1000))).toISOString().split('T')[0]
    this.kGridColumns = [
      { field: 'client_name', title: 'Account', hidden: true, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, aggregates: ["count"], groupHeaderTemplate: "#=data.value# (#= count#)", template: this.openData('client_id', 'client_name', '/clients', 'tz-grid-hlink') },
      { field: 'branch_name', title: 'Branch', hidden: true, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, aggregates: ["count"], groupHeaderTemplate: "#=data.value# (#= count#)", template: this.openData('branch_id', 'branch_name', '/branches', 'tz-grid-hlink') },
      { field: 'fleet_name', title: 'Fleet', hidden: true, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, aggregates: ["count"], groupHeaderTemplate: "#=data.value# (#= count#)", template: this.openData('fleet_id', 'fleet_name', '/fleets', 'tz-grid-hlink') },
      { field: 'completed_date', title: 'Completed', hidden: false, format: "{0:d}", width: this.kColumnWidthDefault },
      { field: 'ro_number', title: 'RO #', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('id', 'ro_number', '/repair_orders', 'tz-grid-hlink') },
      { field: 'invoice_number', title: 'Vendor Reference', hidden: false,filterable: this.kDefaultContains, width: this.kColumnWidthDefault },
      { field: 'vehicle_number', title: 'Vehicle Unit #', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, aggregates: ["count"], groupHeaderTemplate: "#=data.value# (#= count#)", template: this.openData('vehicle_id', 'vehicle_number', '/vehicles', 'tz-grid-hlink') },
      { field: 'vehicle_vin', title: 'VIN Last 5', hidden: true, filterable: this.kDefaultContains, width: this.kColumnWidthDefault },
      { field: 'desc', title: 'Summary of Work Performed', hidden: false, filterable: this.kDefaultContains, width: "250px" },
      { field: 'vendor_name', title: 'Vendor', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, aggregates: ["count"], groupHeaderTemplate: "#=data.value# (#= count#)", template: this.openData('vendor_id', 'vendor_name', '/vendors', 'tz-grid-hlink') },
      { field: 'ros_name', title: 'RO Status', hidden: true, filterable: this.kDefaultContains, width: this.kColumnWidthDefault },
    ]

    this.gridSchema = {
      model: {
        id: "id",
        fields: {
          client_name: {type: 'string'},
          branch_name: {type: 'string'},
          fleet_name: {type: 'string'},
          completed_date: {type: 'date'},
          ro_number: {type: 'string'},
          invoice_number: {type: 'string'},
          vehicle_number: {type: 'string'},
          vehicle_vin: {type: 'string'},
          desc: {type: 'string'},
          vendor_name: {type: 'string'},
          ros_name: {type: 'string'},
        }
      }
    }

  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.gridDataLoaded = false
      this.gridData = []

      await this.$refs.form.validate()
      if (!this.formValid) {
        this.loading = false
        return
      }

      const response = await this.$axios.$get('/reports/invoice_summary', {
        params: {
          _sort: (options.sortBy) ? options.sortBy[0] : null,
          _sort_dir: (options.sortDesc && options.sortDesc[0] == true) ? 'desc' : 'asc',
          filterByClient: (this.filteredClient) ? this.filteredClient.id : null,
          startDate: this.startDate,
          endDate: this.endDate,
        }
      })

      this.gridData = response.data
      for (let key in this.gridData) {
        this.gridData[key]['completed_date'] = new Date((this.gridData[key]['completed_date']).replaceAll(' ','T'))
        for (let keyST in response.summaryTypes) {
          let column = response.summaryTypes[keyST]
          if (this.gridData[key][column] == null || typeof(this.gridData[key][column]) == "undefined") {
            this.gridData[key][column] = 0
          }
        }
      }

      if(!this.dynamicColumnsAdded) {
        for (let key in response.summaryTypes) {
          let column = response.summaryTypes[key]
          this.kGridColumns.push({
            field: column,
            title: column,
            hidden: false,
            format: "{0:c}",
            width: this.kColumnWidthDefault,
            aggregates: ['sum'],
            groupFooterTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#",
            footerTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#"
          })
          this.kGridAggregates.push({ field: column, aggregate: 'sum'})
          this.kGroup[0].aggregates.push({ field: column, aggregate: 'sum'})
          this.gridSchema.model.fields[column] = {type: 'number'}
        }
        this.kGridColumns.push({
          field: 'total_price',
          title: 'Total Cost',
          hidden: false,
          format: "{0:c}",
          width: this.kColumnWidthDefault,
          aggregates: ['sum'],
          groupFooterTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#",
          footerTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#"
        })
        this.kGridAggregates.push({ field: 'total_price', aggregate: 'sum' })
        this.gridSchema.model.fields['total_price'] = {type: 'number'}
        this.dynamicColumnsAdded = true
      }

      for (let key in this.kGridColumns) {
        if (this.kGridColumns[key]['field'] == 'client_name') {
          if (this.filteredClient == null)
            this.kGridColumns[key]['hidden'] = false
          else
            this.kGridColumns[key]['hidden'] = true
          break
        }
      }

      this.kGridAggregates.push({ field: 'client_name', aggregate: 'count'})
      this.kGridAggregates.push({ field: 'branch_name', aggregate: 'count'})
      this.kGridAggregates.push({ field: 'fleet_name', aggregate: 'count'})
      this.kGridAggregates.push({ field: 'vehicle_number', aggregate: 'count'})
      this.kGridAggregates.push({ field: 'vendor_name', aggregate: 'count'})

      if (this.filteredClient == null) {
        this.kExportFileName = "Invoice Summary Report"
      }
      else {
        this.kExportFileName = "Invoice Summary Report " + this.filteredClient.name
      }

      this.loading = false
      this.gridDataLoaded = true
    },
    clearFilters () {
      this.startDate = null
      this.endDate = null
      this.filteredClient = null
      this.refresh()
    },
    refresh () {
      this.showDates = false
      this.$nextTick().then(() => {
        // Add the component back in
        this.showDates = true
      });
    },
    refreshGrid () {
      this.getDataFromApi()
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
    }
  }
}
</script>

<style scoped>
</style>

