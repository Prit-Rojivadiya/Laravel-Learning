<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Fuel Tax Report</h2>
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
                       :key="`this_month`"
                       :label="`This Month`"
                       :value="`this_month`"
              />
              <v-radio class="mr-4"
                       :key="`last_month`"
                       :label="`Last Month`"
                       :value="`last_month`"
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
      <v-row>
        <v-col cols="12">
          <v-radio-group class="ml-2" v-model="detailSummarySelection">
            <v-layout align-start row>
              <v-radio class="mr-4"
                       :key="`summary`"
                       :label="`Summary`"
                       :value="`summary`"
              />
              <v-radio class="mr-4"
                       :key="`detailed`"
                       :label="`Detailed`"
                       :value="`detailed`"
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
        <span v-if="loading">Preparing report, this make take a few minutes...</span>
        <v-progress-circular
          v-if="loading"
          indeterminate
          color="primary"
          class="ml-5"
        ></v-progress-circular>
        <v-col>
          <FuelTaxGrid
            v-if="showGrid"
            :filteredClientId="filteredClientId"
            :filteredBranchId="filteredBranchId"
            :filteredFleetId="filteredFleetId"
            :startDate="startDate"
            :endDate="endDate"
            :reportType="detailSummarySelection"
            @grid-data-loaded="gridDataLoaded"
          />
        </v-col>
      </v-row>
    </div>

  </div>
</template>

<script>

import DatePicker from '~/components/forms/DatePicker'
import ModelNameSearchSelect from '~/components/forms/ModelNameSearchSelect'
import FuelTaxGrid from "~/components/dashboards/fuel_tax/FuelTaxGrid";

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    FuelTaxGrid,
    ModelNameSearchSelect,
    DatePicker
  },
  data: () => ({
    loading: false,
    showClientBranchFleet: true,
    showDates: false,
    showGrid: false,
    showCharts: false,
    dateSelection: 'this_year',
    thisYearStart: null,
    thisYearEnd: null,
    lastYearStart: null,
    lastYearEnd: null,
    thisMonthStart: null,
    thisMonthEnd: null,
    lastMonthStart: null,
    lastMonthEnd: null,
    detailSummarySelection: 'summary',
    formValid: false,
    startDate: null,
    endDate: null,
    filteredClient: null,
    filteredClientId: null,
    filteredBranch: {id: 'all', name: "All"},
    filteredBranchId: null,
    filteredFleet: {id: 'all', name: "All"},
    filteredFleetId: null,
    gridData: [],
    kSeriesType: 'column',
  }),
  created () {
    let now = new Date()  // now is in the current timezone daylight or standard
    // apparently the following commands create the times in standard time in the timezone
    this.thisYearStart = new Date(now.getFullYear(), 0, 1)
    this.lastYearStart = new Date(now.getFullYear()-1, 0, 1)
    this.thisYearEnd = new Date(now.getFullYear(), 11, 31, 23, 59, 59)
    this.lastYearEnd = new Date(now.getFullYear()-1, 11, 31, 23, 59, 59)
    this.thisMonthStart = new Date(now.getFullYear(), now.getMonth(), 1)
    this.lastMonthStart = new Date(now.getFullYear(), now.getMonth()-1, 1)
    this.thisMonthEnd = new Date(now.getFullYear(), now.getMonth()+1, 0, 23, 59, 59)
    this.lastMonthEnd = new Date(now.getFullYear(), now.getMonth(), 0, 23, 59, 59)

    const offset = this.thisYearStart.getTimezoneOffset() // important to get from converted time, and not from 'now', see note above
    //Now handle timezome offset, and convert to yyyy-mm-dd (no time '2022-02-23T19:21:21.469Z')
    this.thisYearStart = (new Date(this.thisYearStart.getTime() - (offset*60*1000))).toISOString().split('T')[0]
    this.lastYearStart = (new Date(this.lastYearStart.getTime() - (offset*60*1000))).toISOString().split('T')[0]
    this.thisYearEnd = (new Date(this.thisYearEnd.getTime() - (offset*60*1000))).toISOString().split('T')[0]
    this.lastYearEnd = (new Date(this.lastYearEnd.getTime() - (offset*60*1000))).toISOString().split('T')[0]
    this.thisMonthStart = (new Date(this.thisMonthStart.getTime() - (offset*60*1000))).toISOString().split('T')[0]
    this.lastMonthStart = (new Date(this.lastMonthStart.getTime() - (offset*60*1000))).toISOString().split('T')[0]
    this.thisMonthEnd = (new Date(this.thisMonthEnd.getTime() - (offset*60*1000))).toISOString().split('T')[0]
    this.lastMonthEnd = (new Date(this.lastMonthEnd.getTime() - (offset*60*1000))).toISOString().split('T')[0]
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
    dateSelectionChanged (val) {
      if (val === 'this_year') {
        this.showDates = false
        this.startDate = this.thisYearStart
        this.endDate = this.thisYearEnd
      }
      else if (val === 'last_year') {
        this.showDates = false
        this.startDate = this.lastYearStart
        this.endDate = this.lastYearEnd
      }
      else if (val === 'this_month') {
        this.showDates = false
        this.startDate = this.thisMonthStart
        this.endDate = this.thisMonthEnd
      }
      else if (val === 'last_month') {
        this.showDates = false
        this.startDate = this.lastMonthStart
        this.endDate = this.lastMonthEnd
      }
      else if (val === 'custom_pick') {
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
      })
    },
    gridDataLoaded (gridData, monthlyColumns) {
      this.loading = false
    },
    clearFilters () {
      this.showDates = false
      this.dateSelection = 'this_year'
      this.startDate = this.thisYearStart
      this.endDate = this.thisYearEnd
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

