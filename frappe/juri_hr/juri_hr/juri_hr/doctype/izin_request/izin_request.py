import frappe
from frappe import _
from frappe.model.document import Document


class IzinRequest(Document):
    def validate(self):
        self.validate_dates()
        self.validate_overlap()

    def validate_dates(self):
        if not self.from_date or not self.to_date:
            frappe.throw(_("Tanggal izin wajib diisi"))
        if self.to_date < self.from_date:
            frappe.throw(_("Tanggal akhir tidak boleh sebelum tanggal mulai"))

    def validate_overlap(self):
        filters = {
            "employee": self.employee,
            "docstatus": ["<", 2],
            "name": ["!=", self.name or "NEW"],
            "from_date": ["<=", self.to_date],
            "to_date": [">=", self.from_date],
        }
        overlapping = frappe.get_all("Izin Request", filters=filters, limit=1)
        if overlapping:
            frappe.throw(_("Izin bertabrakan dengan pengajuan lain pada rentang tanggal tersebut"))

    def on_submit(self):
        self.status = "Approved"

    def on_cancel(self):
        self.status = "Cancelled"
