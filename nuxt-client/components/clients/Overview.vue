<template>
  <v-container grid-list-sm justify-space-around fluid class="p-4">
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Name:</span>
            <span>{{ client.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Abbrv:</span>
            <span>{{ client.abbrv }}</span>
          </div>
        </div>
      </v-flex>
      <v-flex v-if="activeIntegrationsStr != null">
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Enabled Integrations:</span>
            <span>{{ activeIntegrationsStr }}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  name: "ClientOverview",
  props: ['value', 'client', 'integrations'],
  data: () => ({
    working: false,
    activeIntegrationsStr: null
  }),
  created () {
    let activeIntegrations = []
    for (const tIntegration of this.integrations) {
      if (tIntegration.active) {
        activeIntegrations.push(tIntegration)
      }
    }

    if (activeIntegrations && activeIntegrations.length > 0) {
      this.activeIntegrationsStr = activeIntegrations[0].name
    }
    let index = 0
    for (const aIntegration of activeIntegrations) {
      if ((aIntegration.active === true || aIntegration.active === 1) && (index > 0)) {
        this.activeIntegrationsStr = this.activeIntegrationsStr + ', ' + aIntegration.name
      }
      index++
    }
  }
}
</script>

<style scoped>
  .v-card .flex-item {
    margin-bottom: 5px;
  }
</style>
