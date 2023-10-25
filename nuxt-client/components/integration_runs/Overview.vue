<template>
  <v-container grid-list-sm justify-space-around fluid class="p-4">
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Task:</span>
            <span>{{ integrationRun.task | formatSplitWordsByCapitalize}}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Started At:</span>
            <span>{{ integrationRun.started | formatDateMDYhhmmss }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Total Items:</span>
            <span>{{ integrationRun.total }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Successful:</span>
            <span>{{ integrationRun.success_count }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Failed:</span>
            <span>{{ integrationRun.failed_count }}</span>
          </div>
        </div>
      </v-flex>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Status:</span>
            <span>{{ integrationRun.status | formatCapitalize}}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Completed At:</span>
            <span>{{ integrationRun.completed | formatDateMDYhhmmss }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Summary:</span>
            <span>{{ integrationRun.summary_msg }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Errors:</span>
            <span>{{ integrationRun.error_msg }}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  name: 'IntegrationRunOverview',
  props: ['value', 'integrationRun'],
  data: () => ({
    loading: false
  }),
  async created () {
  }
}
</script>

<style scoped>
  .v-card .flex-item {
    margin-bottom: 5px;
  }
</style>
