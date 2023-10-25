<template>
  <div>
    <div class="mb-5">
      <v-row>
        <v-col>
          <h2>Manage Line Item Category</h2>
        </v-col>
      </v-row>
      <v-row>
        <v-col>
          <v-btn small color="info" style="float: right" @click="editLineItemCategory">Edit</v-btn>
          <v-dialog
            v-model="deleteDialog"
            persistent
            max-width="290"
          >
            <template v-slot:activator="{ on, attrs }">
              <v-btn small
                     class="mr-5"
                     color="#fe8181"
                     style="float:right"
                     v-bind="attrs"
                     v-on="on">
                Delete
              </v-btn>
            </template>
            <v-card>
              <v-card-title class="headline">Are you sure?</v-card-title>
              <v-card-text>Do you really want to delete?</v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="green darken-1" text @click="deleteDialog = false">Cancel</v-btn>
                <v-btn color="red" @click="deleteItem">Delete</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>

        </v-col>
      </v-row>
    </div>

    <v-card>
      <v-card-title>
        {{ line_item_category.name }}
      </v-card-title>
      <v-card-text>
        <LineItemCategoryOverviewComponent
          v-if="!loading"
          :line_item_category="line_item_category"
          v-model="line_item_category"
        />
      </v-card-text>
    </v-card>

    <ManageLineItemCategoryComponent
      v-if="showEditLineItemCategory"
      :line_item_category="line_item_category"
      v-model="showEditLineItemCategory"
      @line_item_category-saved="getDataFromApi"
    />

    <v-btn small color="info" style="float: left" class="mt-5" @click="goBack">Back</v-btn>
  </div>
</template>

<script>

import LineItemCategoryOverviewComponent from '~/components/line_item_categories/Overview'
import ManageLineItemCategoryComponent from '~/components/line_item_categories/ManageLineItemCategory'


export default {
  middleware: 'auth',
  layout: 'loggedin',
  components: {
    LineItemCategoryOverviewComponent,
    ManageLineItemCategoryComponent
  },
  data: () => ({
    loading: true,
    deleteDialog: false,
    showEditLineItemCategory: false,
    line_item_category: {
      name: null,
      code: null,
      desc: null,
    },
  }),
  async created () {
    await this.getDataFromApi()
  },
  methods: {
    async getDataFromApi(options) {
      this.loading = true
      this.line_item_category = await this.$axios.$get(`line_item_categories/${this.$route.params.id}`)
      this.loading = false
    },
    editLineItemCategory () {
      this.showEditLineItemCategory = true
    },
    async deleteItem () {
      await this.$axios.$delete(`line_item_categories/${this.$route.params.id}`)
      this.deleteDialog = false
      this.$router.back()
    },
    async goBack () {
      this.$router.back()
    }
  }
}
</script>
