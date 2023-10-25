<template>
  <v-layout row class="align-center layout px-4 pt-4 app--page-header">
    <v-img src="/img/sssf-logo.png" max-height="100" contain />
  </v-layout>
</template>

<script>
export default {
  data () {
    return {
      title: ''
    }
  },
  computed: {
    breadcrumbs () {
      // const breadcrumbs = []
      // menu.forEach((item) => {
      //   console.log(menu)
      // })
      //   if (item.items) {
      //     const child = item.items.find((i) => {
      //       return i.href === this.$route.path
      //     })
      //     if (child) {
      //       breadcrumbs.push({ text: item.title })
      //       breadcrumbs.push({ text: child.title, disabled: true })
      //       this.title = child.title
      //     }
      //   } else if (item.href === this.$route.path) {
      //     this.title = item.title
      //     breadcrumbs.push({ text: item.title })
      //   }
      // })
      return []
    }
  }
}
</script>
