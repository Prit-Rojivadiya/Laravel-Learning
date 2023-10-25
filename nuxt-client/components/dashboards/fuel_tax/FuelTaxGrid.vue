<template>
  <KGrid
    class="compact-grid"
    v-if="gridDataLoaded"
    :height="700"
    :kGridData="gridData"
    :kSchema="gridSchema"
    :kColumns="kGridColumns"
    :kAggregate="kGridAggregates"
    :kToolBar="['excel', 'pdf']"
    :kPageSize="100"
    :kExportFileName="kExportFileName"
    @grid-filtered-update="gridFilteredUpdate"
    @force_refresh_grid="refreshGrid"
  />
</template>

<script>

import KGrid from "~/components/kendo/grid2/Grid"

export default {
  name: "CPMMonthByCategoryGrid",
  props: ['filteredClientId','filteredBranchId','filteredFleetId','startDate','endDate','reportType'],
  components: {
    KGrid,
  },
  data: () => ({
    gridDataLoaded: false,
    gridData: [],
    gridSchema: {},
    kGridColumns: [],
    kColumnWidthDefault: "225px",
    kDefaultContains: {cell: {operator: 'contains'}},
    kGridAggregates: [],
    kExportFileName: null,
    dynamicColumnsAdded: false,
  }),
  async created () {
    this.gridDataLoaded = false
    let hidden = false
    this.kGridColumns = []
    this.kGridColumns.push({ field: 'vehicle_number', title: 'Vehicle', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('vehicle_id', 'vehicle_number', '/vehicles', 'tz-grid-hlink') })
    hidden = (this.filteredClientId && this.filteredClientId != 'all')
    this.kGridColumns.push({ field: 'client_name', title: 'Account', hidden: true, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('client_id', 'client_name', '/clients', 'tz-grid-hlink') })
    hidden = (this.filteredBranchId && this.filteredBranchId != 'all')
    this.kGridColumns.push({ field: 'branch_name', title: 'Branch', hidden: hidden, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('branch_id', 'branch_name', '/branches', 'tz-grid-hlink') })
    hidden = (this.filteredFleetId && this.filteredFleetId != 'all')
    this.kGridColumns.push({ field: 'fleet_name', title: 'Fleet', hidden: hidden, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('fleet_id', 'fleet_name', '/fleets', 'tz-grid-hlink') })
    this.kGridColumns.push({ field: 'location_state', title: 'State', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault })
    this.kGridColumns.push({ field: 'enter_meter', title: 'Enter Meter',format: "{0:n}", hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault })
    this.kGridColumns.push({ field: 'exit_meter', title: 'Exit Meter', format: "{0:n}", hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault })
    this.kGridColumns.push({ field: 'distance', title: 'Distance Traveled', format: "{0:n0}", hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, aggregates: ['sum'], groupFooterTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'n0')#", footerTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'n0')#" })
    this.kGridColumns.push({ field: 'location_country', title: 'Country', hidden: true, filterable: this.kDefaultContains, width: this.kColumnWidthDefault })
    this.kGridColumns.push({ field: 'enter_meter_reading_date', title: 'Enter Date', hidden: false, format: '{0:MM/dd/yyyy HH:mm:ss}', width: this.kColumnWidthDefault })
    this.kGridColumns.push({ field: 'exit_meter_reading_date', title: 'Exit Date', hidden: false, format: '{0:MM/dd/yyyy HH:mm:ss}', width: this.kColumnWidthDefault })
    // this.kGridColumns.push({ field: 'meter_reading_type', title: 'Measurement', hidden: true, filterable: this.kDefaultContains, width: this.kColumnWidthDefault })
    this.kGridAggregates.push({ field: 'distance', aggregate: 'sum' })

    this.gridSchema = {
      model: {
        id: "id",
        fields: {
          vehicle_number: {type: 'string'},
          client_name: {type: 'string'},
          branch_name: {type: 'string'},
          fleet_name: {type: 'string'},
          source: {type: 'string'},
          enter_meter: {type: 'number'},
          exit_meter: {type: 'number'},
          distance: {type: 'number'},
          location_country: {type: 'string'},
          location_state: {type: 'string'},
          enter_meter_reading_date: {type: 'date'},
          exit_meter_reading_date: {type: 'date'},
          meter_reading_type: {type: 'string'},
        }
      }
    }

    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.gridDataLoaded = false
      this.gridData = []

      const reportStartDate = new Date(this.startDate + 'T00:00:00Z') //convert yyyy-mm-dd string into timzone specific dates
      const reportEndDate = new Date(this.endDate + 'T23:59:59Z') //convert yyyy-mm-dd string into timzone specific dates

      const response = await this.$axios.$get('/reports/fuel_tax', {
        params: {
          _sort:  null,
          _sort_dir: 'desc',
          filterByClient: (this.filteredClientId && this.filteredClientId != 'all') ? this.filteredClientId : null,
          filterByBranch: (this.filteredBranchId && this.filteredBranchId != 'all') ? this.filteredBranchId : null,
          filterByFleet: (this.filteredFleetId && this.filteredFleetId != 'all') ? this.filteredFleetId : null,
          startDate: reportStartDate,
          endDate: reportEndDate,
          reportType: this.reportType
        }
      })

      this.gridData = response.data
      for (let key in this.kGridColumns) {
        // if (this.kGridColumns[key]['field'] == 'client_name') {
        //   if (this.filteredClientId == null)
        //     this.kGridColumns[key]['hidden'] = false
        //   else
        //     this.kGridColumns[key]['hidden'] = true
        //   break
        // }
      }
      if (this.filteredClientId == null) {
        this.kExportFileName = "Fuel Tax Report"
      }
      else {
        this.kExportFileName = "Fuel Tax Report"
        //this.kExportFileName = "Fuel Tax Report " + this.filteredClientId
      }

      this.$emit('grid-data-loaded', this.gridData)
      this.gridDataLoaded = true
    },
    gridFilteredUpdate (filteredData) {
      this.$emit('grid-filtered-update', filteredData)
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
