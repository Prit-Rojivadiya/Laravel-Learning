<template>
  <v-container grid-list-sm justify-space-around fluid class="p-4">
    <v-layout row>
      <v-flex>
        <div class="flex-content">
          <div class="flex-item">
            <span class="grey--text">Line Item Type:</span>
            <span>{{ line_item_category.line_item_type.name }}</span>
          </div>
          <div class="flex-item">
            <span class="grey--text">Name:</span>
            <span>{{ line_item_category.name }}</span>
          </div>
          <!--
          <div class="flex-item">
            <span class="grey--text">Code:</span>
            <span>{{ line_item_category.code }}</span>
          </div>
          -->
          <div class="flex-item">
            <span class="grey--text">Description:</span>
            <span>{{ line_item_category.desc }}</span>
          </div>
        </div>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
export default {
  name: "LineItemCategoryOverview",
  props: ['value', 'line_item_category'],
  data: () => ({
    working: false,
  }),
  async created () {
  },
}
</script>

<style scoped>
  .v-card .flex-item {
    margin-bottom: 5px;
  }
</style>
