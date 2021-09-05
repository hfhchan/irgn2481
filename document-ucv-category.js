
class UcvCategory extends HTMLElement {
	constructor() {
		super();
		this.initialized = false;
	}

	connectedCallback() {
		if (this.initialized) {
			return;
		}
		this.initialized = true;
		this.style.display = 'block';
		this.loadData();
	}

	async loadData() {
		const url = new URLSearchParams();
		url.append('action', 'category');
		url.append('group', this.dataset.group);
		url.append('glyph', this.dataset.glyph);
		url.append('original', this.dataset.original ? '1' : '');
		const res = await fetch('?' + url.toString());
		const html = await res.text();
		this.innerHTML = html;
	}
}
customElements.define('ucv-category', UcvCategory);
