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
  name: 'VehiclesGrid2',
  props: ['filteredClientId','filteredBranchId','filteredFleetId','filteredByName'],
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
    this.kGridColumns.push({ field: 'vehicle_number', title: 'Vehicle', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, template: this.openData('id', 'vehicle_number', '/vehicles', 'tz-grid-hlink') })
    hidden = (this.filteredClientId && this.filteredClientId != 'all')
    this.kGridColumns.push({ field: 'client_name', title: 'Account', hidden: hidden, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, aggregates: ["count"], groupHeaderTemplate: "#=data.value# (#= count#)", template: this.openData('client_id', 'client_name', '/clients', 'tz-grid-hlink') })
    hidden = (this.filteredBranchId && this.filteredBranchId != 'all')
    this.kGridColumns.push({ field: 'branch_name', title: 'Branch', hidden: hidden, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, aggregates: ["count"], groupHeaderTemplate: "#=data.value# (#= count#)", template: this.openData('branch_id', 'branch_name', '/branches', 'tz-grid-hlink') })
    hidden = (this.filteredFleetId && this.filteredFleetId != 'all')
    this.kGridColumns.push({ field: 'fleet_name', title: 'Fleet', hidden: hidden, filterable: this.kDefaultContains, width: this.kColumnWidthDefault, aggregates: ["count"], groupHeaderTemplate: "#=data.value# (#= count#)", template: this.openData('fleet_id', 'fleet_name', '/fleets', 'tz-grid-hlink') })
    this.kGridColumns.push({field: 'in_service_date', title: 'In Service Date', hidden: false, format: '{0:MM/dd/yyyy}', width: this.kColumnWidthDefault})
    this.kGridColumns.push({field: 'year', title: 'Year', hidden: false, format: "{0:0}", width: this.kColumnWidthDefault})
    this.kGridColumns.push({field: 'make', title: 'Make', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
    this.kGridColumns.push({field: 'model', title: 'Model', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
    this.kGridColumns.push({field: 'vin', title: 'Vin', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
    this.kGridColumns.push({field: 'tire_size', title: 'Tire Size', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
    this.kGridColumns.push({field: 'license_plate_number', title: 'License Plate Number', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
    this.kGridColumns.push({field: 'license_state', title: 'License State', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
    this.kGridColumns.push({field: 'engine_serial_number', title: 'Engine Serial Number', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
    this.kGridColumns.push({field: 'vehicle_type_name', title: 'Vehicle Type', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
    this.kGridColumns.push({field: 'engine_manufacturer_name', title: 'Engine Manufacturer', hidden: false, filterable: this.kDefaultContains, width: this.kColumnWidthDefault})
    this.kGridColumns.push({field: 'purchase_price', title: 'Purchase Price', hidden: false, format: "{0:c}", width: this.kColumnWidthDefault, aggregates: ['sum'], groupFooterTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#", footerTemplate: "Total: #=kendo.toString(kendo.parseFloat(sum), 'c')#"})

    this.gridSchema = {
      model: {
        id: "id",
        fields: {
          vehicle_number: {type: 'string'},
          client_name: {type: 'string'},
          branch_name: {type: 'string'},
          fleet_name: {type: 'string'},
          purchase_price: {type: 'number'},
          in_service_date: {type: 'date'},
          year: {type: 'number'},
          make: {type: 'string'},
          model: {type: 'string'},
          vin: {type: 'string'},
          tire_size: {type: 'string'},
          license_plate_number: {type: 'string'},
          license_state: {type: 'string'},
          engine_serial_number: {type: 'string'},
          vehicle_type_name: {type: 'string'},
          engine_manufacturer_name: {type: 'string'}
        }
      }
    }
    this.kGridAggregates = [
      {field: 'client_name', aggregate: "count"},
      {field: 'branch_name', aggregate: "count"},
      {field: 'fleet_name', aggregate: "count"},
      {field: 'purchase_price', aggregate: "sum"}
    ]

    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.gridDataLoaded = false
      this.gridData = []

      const response = await this.$axios.$get('vehicles', {
        params: {
          _sort:  null,
          _sort_dir: 'desc',
          filterByClient: (this.filteredClientId && this.filteredClientId != 'all') ? this.filteredClientId : null,
          filterByBranch: (this.filteredBranchId && this.filteredBranchId != 'all') ? this.filteredBranchId : null,
          filterByFleet: (this.filteredFleetId && this.filteredFleetId != 'all') ? this.filteredFleetId : null,
          filterByName: this.filteredByName
        }
      })

      for (let key in response.data) {
        let data = response.data[key]
        if (data.license_state) {
          data.license_state = data.license_state.toLowerCase()
            .split(' ')
            .map((s) => s.charAt(0).toUpperCase() + s.substring(1))
            .join(' ')
        }
      }
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
        this.kExportFileName = "Vehicles"
      }
      else {
        this.kExportFileName = "Vehicles"
        //this.kExportFileName = "Vehicles " + this.filteredClientId
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
