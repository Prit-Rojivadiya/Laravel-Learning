<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>File Import Manager</h2>
        </v-col>
      </v-row>
    </div>

    <div>
      <v-card class="mt-5">
        <v-tabs v-model="default_tab" show-arrows >
          <v-tabs-slider color="teal lighten-3"></v-tabs-slider>
          <v-tab key="fuelings">Fuel Events</v-tab>

          <v-tab-item key="fuelings">
            <v-card-text>
              <FuelingsCSVImport/>
            </v-card-text>
          </v-tab-item>
        </v-tabs>

      </v-card>
    </div>

  </div>
</template>

<script>

import FuelingsCSVImport from '~/components/fuelings/FuelingsCSVImport'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    FuelingsCSVImport,
  },
  data: () => ({
    default_tab: 'fuelings',
  }),
  async created () {
  },
  methods: {
  }
}
</script>

<style scoped>
</style>

