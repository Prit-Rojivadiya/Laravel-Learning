<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Line Item Categories</h2>
        </v-col>
        <v-col>
          <v-btn small color="info" style="float: right" @click="addLineItemCategory">Add Line Item Category</v-btn>
        </v-col>
      </v-row>
    </div>

    <LineItemCategoriesGridComponent
      v-if="showLineItemCategories"
      :lineItemType="lineItemType"
      :showAddLineItemCategoryInGrid="showAddLineItemCategoryInGrid"
      :showLineItemTypeInGrid = "showLineItemTypeInGrid"
      v-model="showLineItemCategories"
    />

    <ManageLineItemCategoryComponent
      v-if="addingLineItemCategory"
      v-model="addingLineItemCategory"
      :lineItemType="lineItemType"
      @line_item_category-saved="refresh"
    />

  </div>

</template>

<script>

import LineItemCategoriesGridComponent from '~/components/line_item_categories/LineItemCategoriesGrid'
import ManageLineItemCategoryComponent from '~/components/line_item_categories/ManageLineItemCategory'

export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    LineItemCategoriesGridComponent,
    ManageLineItemCategoryComponent
  },
  data: () => ({
    loading: true,
    addingLineItemCategory: false,
    showLineItemCategories: true,
    showLineItemTypeInGrid: true,
    showAddLineItemCategoryInGrid: false,
    lineItemType: null,
  }),
  methods: {
    addLineItemCategory () {
      this.addingLineItemCategory = true
    },
    refresh () {
      this.showLineItemCategories = false
      this.$nextTick().then(() => {
        // Add the component back in
        this.showLineItemCategories = true
      });
    }

  }
}
</script>

<style scoped>

</style>
