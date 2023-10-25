<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Cost Per Mile Detailed (CPM) Report</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="primary" style="float: right" class="ml-5 mb-2" :loading="loading" @click="runReport">Run Report</v-btn>
          <v-btn small style="float: right;" class="ml-5 mb-2" :disabled="loading" @click="clearFilters">Clear Filters</v-btn>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12">
          <v-radio-group class="ml-2" v-model="reportTypeSelection">
        <!--<template v-slot:label>
              <v-label>
                <div class="tz_radio_group_label">
                  Report Type
                </div>
              </v-label>
            </template>-->
            <v-layout align-start row>
              <v-radio class="mr-4"
                       :key="`by_month`"
                       :label="`By Month`"
                       :value="`by_month`"
              />
              <v-radio class="mr-4"
                       :key="`by_category`"
                       :label="`By Category`"
                       :value="`by_category`"
              />
            </v-layout>
          </v-radio-group>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12">
          <v-radio-group class="ml-2" v-model="reportUnitType">
            <v-layout align-start row>
              <v-radio class="mr-4"
                       :key="`branch`"
                       :label="`Branch`"
                       :value="`branch`"
              />
              <v-radio class="mr-4"
                       :key="`fleet`"
                       :label="`Fleet`"
                       :value="`fleet`"
              />
              <v-radio class="mr-4"
                       :key="`vehicle`"
                       :label="`Vehicle`"
                       :value="`vehicle`"
              />
            </v-layout>
          </v-radio-group>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12">
          <v-radio-group class="ml-2" v-model="dateSelection" @change="dateSelectionChanged">
            <v-layout align-start row>
              <v-radio class="mr-4"
                       :key="`this_year`"
                       :label="`This Year`"
                       :value="`this_year`"
              />
              <v-radio class="mr-4"
                       :key="`last_year`"
                       :label="`Last Year`"
                       :value="`last_year`"
              />
              <v-radio class="mr-4"
                       :key="`custom_pick`"
                       :label="`Pick Date Range`"
                       :value="`custom_pick`"
              />
            </v-layout>
          </v-radio-group>
        </v-col>
      </v-row>
      <v-form ref="form" v-model="formValid">
        <v-row>
          <v-col cols="4">
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
          <v-col cols="4">
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
          <v-col cols="4">
            <ModelNameSearchSelect
              v-model="filteredClient"
              dense
              :prefill="true"
              :search_label="'Account'"
              :api_url_base="'clients'"
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
      </v-form>
    </div>

    <div>
      <v-row>
        <span v-if="loading">Gathering report data, this make take a few minutes...</span>
        <v-progress-circular
          v-if="loading"
          indeterminate
          color="primary"
          class="ml-5"
        ></v-progress-circular>

        <v-col>
          <CPMUnitByMonthGrid
            v-if="showGrid"
            :unitType="reportUnitType"
            :filteredClientId="filteredClientId"
            :filteredBranchId="filteredBranchId"
            :filteredFleetId="filteredFleetId"
            :startDate="startDate"
            :endDate="endDate"
            @grid-data-loaded="gridDataLoaded"
            @grid-filtered-update="gridFiltered"
          />
        </v-col>
      </v-row>
      <v-row class="mt-5" v-if="(showGrid && showCharts)">
        <v-col>
          <v-radio-group class="ml-2" v-model="kSeriesType" @change="changeChartType">
            <v-layout align-start row>
              <v-radio class="mr-4"
                       :key="`column`"
                       :label="`Bar Chart`"
                       :value="`column`"
              />
              <v-radio class="mr-4"
                       :key="`line`"
                       :label="`Line Chart`"
                       :value="`line`"
              />
            </v-layout>
          </v-radio-group>
        </v-col>
      </v-row>
      <v-row >
        <v-col cols="12">
          <ExpensesTotalByMonthLine
            v-if="(showGrid && showCharts)"
            :kSeriesType="kSeriesType"
            :gridData="totalsByMonthData"
          />
        </v-col>
      </v-row>
    </div>

  </div>
</template>

<script>

import DatePicker from '~/components/forms/DatePicker'
import ModelNameSearchSelect from '~/components/forms/ModelNameSearchSelect'
import ExpensesTotalByMonthLine from "~/components/dashboards/expenses/TotalByMonthLine";
//import ExpensesByCategoryPie from "~/components/dashboards/expenses/TotalByCategoryPie";
//import CPMTotalByMonthLine from "~/components/dashboards/cost_per_mile/TotalByMonthLine";
//import DistanceByMonthLine from "~/components/dashboards/distance/DistanceByMonthLine";
import CPMUnitByMonthGrid from "~/components/dashboards/cost_per_mile/UnitByMonthGrid";

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    CPMUnitByMonthGrid,
    //DistanceByMonthLine,
    ExpensesTotalByMonthLine,
    //ExpensesByCategoryPie,
    //CPMTotalByMonthLine,
    ModelNameSearchSelect,
    DatePicker,
  },
  data: () => ({
    loading: false,
    showClientBranchFleet: true,
    showDates: false,
    showGrid: false,
    showCharts: false,
    dateSelection: 'this_year',
    reportTypeSelection: 'by_month',
    reportUnitType: 'branch',
    thisYearStart: null,
    thisYearEnd: null,
    lastYearStart: null,
    lastYearEnd: null,
    formValid: false,
    startDate: null,
    endDate: null,
    filteredClient: null,
    filteredClientId: null,
    filteredBranch: {id: 'all', name: "All"},
    filteredBranchId: null,
    filteredFleet: {id: 'all', name: "All"},
    filteredFleetId: null,
    monthlyColumns: null,
    gridData: [],
    totalsByMonthData: [],
    kSeriesType: 'column',
  }),
  created () {
    let now = new Date()  // now is in the current timezone daylight or standard
    // apparently the following commands create the times in standard time in the timezone
    this.thisYearStart = new Date(now.getFullYear(), 0, 1)
    this.lastYearStart = new Date(now.getFullYear()-1, 0, 1)
    this.thisYearEnd = new Date(now.getFullYear(), 11, 31, 23, 59, 59)
    this.lastYearEnd = new Date(now.getFullYear()-1, 11, 31, 23, 59, 59)

    const offset = this.thisYearStart.getTimezoneOffset() // important to get from converted time, and not from 'now', see note above
    //Now handle timezome offset, and convert to yyyy-mm-dd (no time '2022-02-23T19:21:21.469Z')
    this.thisYearStart = (new Date(this.thisYearStart.getTime() - (offset*60*1000))).toISOString().split('T')[0]
    this.lastYearStart = (new Date(this.lastYearStart.getTime() - (offset*60*1000))).toISOString().split('T')[0]
    this.thisYearEnd = (new Date(this.thisYearEnd.getTime() - (offset*60*1000))).toISOString().split('T')[0]
    this.lastYearEnd = (new Date(this.lastYearEnd.getTime() - (offset*60*1000))).toISOString().split('T')[0]
    //default to this year
    this.startDate = this.thisYearStart
    this.endDate = this.thisYearEnd
  },
  methods: {
    async runReport(options) {
      this.loading = true
      await this.$refs.form.validate()
      if (!this.formValid) {
        this.loading = false
        return
      }
      this.showGrid = false
      this.showCharts = false
      this.$nextTick().then(() => {
        this.showGrid = true
      })
    },
    gridDataLoaded (gridData, monthlyColumns) {
      this.monthlyColumns = monthlyColumns
      this.updateChartsData(gridData)
      this.loading = false
    },
    gridFiltered (gridData) {
      this.updateChartsData(gridData)
    },
    dateSelectionChanged (val) {
      if (val == 'this_year') {
        this.showDates = false
        this.startDate = this.thisYearStart
        this.endDate = this.thisYearEnd
      }
      else if (val == 'last_year') {
        this.showDates = false
        this.startDate = this.lastYearStart
        this.endDate = this.lastYearEnd
      }
      else if (val == 'custom_pick') {
        this.showDates = true
      }
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
      });
    },
    updateChartsData (filterOnlyData) {
      this.showCharts = false
      if (filterOnlyData) {
        this.gridData = filterOnlyData
        this.totalsByMonthData = []
        //initialize data for line chart for totals by month
        for (let key in this.monthlyColumns) {
          let monthKey = this.monthlyColumns[key]
          this.totalsByMonthData[monthKey] = {}
          this.totalsByMonthData[monthKey]['month'] = monthKey
          this.totalsByMonthData[monthKey]['total_cost'] = 0
        }
        //now sum up the totals for each month
        for (let row of this.gridData) {
          for (let keyM in this.monthlyColumns) {
            let monthKey = this.monthlyColumns[keyM]
            this.totalsByMonthData[monthKey]['total_cost'] = this.totalsByMonthData[monthKey]['total_cost'] + row[monthKey]
          }
        }
      }
      else {
        this.gridData = []
      }
      this.$nextTick().then(() => {
        this.showCharts = true
      });
    },
    changeChartType (val) {
      if (val == 'line') {
        this.showCharts = false
        this.$nextTick().then(() => {
          this.showCharts = true
        });
      }
      else if (val == 'column') {
        this.showCharts = false
        this.$nextTick().then(() => {
          this.showCharts = true
        });
      }
    },
    clearFilters () {
      this.showDates = false
      this.dateSelection = 'this_year'
      this.startDate = this.thisYearStart
      this.endDate = this.thisYearEnd
      this.filteredClient=null
      this.filteredClientId=null
      this.filteredBranch={id: 'all', name: "All"}
      this.filteredBranchId=null
      this.filteredFleet={id: 'all', name: "All"}
      this.filteredFleetId=null
    },
  }
}
</script>

<style scoped>
  .tz_radio_group_label {
    margin-bottom: 15px;
    margin-left: -10px;
  }

</style>

