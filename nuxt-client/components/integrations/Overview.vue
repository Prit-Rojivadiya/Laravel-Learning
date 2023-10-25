<template>
  <v-container grid-list-sm justify-space-around fluid class="p-4 pb-0">
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Enabled:</span>
            <span>{{ integrationEnabledStr }}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  name: "IntegrationOverview",
  props: ['value', 'integration'],
  data: () => ({
    working: false,
    integrationEnabledStr: null
  }),
  async created () {
    if (this.integration.active) {
      this.integrationEnabledStr = "True"
    }
    else {
      this.integrationEnabledStr = "False"
    }
  },
}
</script>

<style scoped>
  .v-card .flex-item {
    margin-bottom: 5px;
  }
</style>
