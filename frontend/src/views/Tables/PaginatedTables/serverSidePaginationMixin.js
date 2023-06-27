export default {
	computed: {
		pagesNumber() {
			if (!this.pagination.meta.to) {
				return []
			}
			let from = this.currentPage - this.offset
			if (from < 1) {
				from = 1
			}
			this.last_page = Math.ceil(this.totalData / this.perPage);
			let to = from + (this.offset * 2)
			if (to >= this.last_page) {
				to = this.last_page
			}
			let pagesArray = []
			for (let page = from; page <= to; page++) {
				pagesArray.push(page)
			}
			return pagesArray
		},
		// totalData() {
		//   return (this.pagination.meta.to - this.pagination.meta.from) + 1
		// }
	},
	data() {
		return {
			pagination: {
				meta: {to: 1, from: 1}
			},
			total: 0,
			offset: 4,
			currentPage: 1,
			last_page: "",
			perPage: 50,
			totalData: 0,
			perPageOptions: [10, 20, 30, 40, 50, 100, 150, 200, 300, 400, 500, 600, 700, 800, 900, 1000],
			sortedColumn: 0,
			order: 'asc'
		};
	},
	methods: {
		serialNumber(key) {
			return (this.currentPage - 1) * this.perPage + 1 + key
		},
		changePage(pageNumber) {
			this.currentPage = pageNumber
			this.fetchData()
		},
		sortByColumn(column) {
			if (column === this.sortedColumn) {
				this.order = (this.order === 'asc') ? 'desc' : 'asc'
			} else {
				this.sortedColumn = column
				this.order = 'asc'
			}
			this.fetchData()
		}
	},
	
};
