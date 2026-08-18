import frappe
from frappe import _


@frappe.whitelist(allow_guest=True)
def ping():
    """Health check dipakai frontend SPA untuk verifikasi koneksi & versi app."""
    return {
        "app": "juri_hr",
        "version": frappe.get_attr("juri_hr.__version__"),
        "site": frappe.local.site,
        "logged_in": bool(frappe.session.user and frappe.session.user != "Guest"),
    }
