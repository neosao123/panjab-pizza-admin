let csrf = document.querySelector('meta[name="csrf-token"]').content;
let errorsDiv = $("#errors");
let excelDiv = document.getElementById("imported-excel");
let excelData = [];
let uploadBtn = $("button#btn-upload");
let importedTitle = document.getElementById("imported-title");
let rowCount = 0;

$(function () {
  $.ajaxSetup({
    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
  });
  // Developer: Shreyas Mahamuni
  const templateConfig = ["postalcodes"];
  // Developer: Shreyas Mahamuni
  $("#storeLocation").select2({
    placeholder: "Select a Store Location",
    allowClear: true,
    ajax: {
      url: baseUrl + "/zipcode/fetch/get-store-location",
      type: "get",
      dataType: "json",
      data: function (params) {
        return {
          search: params.term,
        };
      },
      processResults: function (response) {
        return {
          results: response,
        };
      },
      cache: true,
      delay: 200,
    },
  });

  // Developer: Shreyas Mahamuni
  function showErrorsForExcel(title, message, isError) {
    importedTitle.innerHTML = title;
    if (isError) {
      importedTitle.classList.remove("text-success");
      importedTitle.classList.remove("text-danger");
      importedTitle.classList.add("text-danger");
      errorsDiv.append(`<p>${message}</p>`);
    } else {
      importedTitle.classList.remove("text-success");
      importedTitle.classList.remove("text-danger");
      importedTitle.classList.add("text-success");
    }
  }

  // Developer: Shreyas Mahamuni
  $("button#btn-import").on("click", function () {
    $storelocation = $("#storeLocation").val() ?? "";
    if ($storelocation !== "") {
      var fileUpload = document.getElementById("zipcodeFile");
      if (fileUpload.value != "") {
        if (typeof FileReader != "undefined") {
          $(this).attr("disabled", true);
          $(this).text("Reading excel data....");
          var reader = new FileReader();
          reader.onload = function (e) {
            $(this).prop("disabled", true).text("Reading Excel Data....");
            var data = new Uint8Array(e.target.result);
            ProcessExcel(data);
          };

          reader.readAsArrayBuffer(fileUpload.files[0]);
        } else {
          toastr.error(
            "This browser does not support file upload and processing. Please try with another modern browser.",
            "Excel File",
            {
              progressBar: true,
            }
          );
        }
      } else {
        toastr.error(
          "Have you selected the participants excel data file? Please select an proper excel file.",
          "File Missing",
          {
            progressBar: true,
          }
        );
      }
    } else {
      toastr.error(
        "Please fill all required fields.",
        "Select Store Location",
        {
          progressBar: true,
        }
      );
    }
  });

  // Developer: Shreyas Mahamuni
  function ProcessExcel(data) {
    $("div#xlx-section").hide();
    var workbook = XLSX.read(data, { type: "array" });

    var firstSheet = workbook.SheetNames[0];

    var excelRows = XLSX.utils.sheet_to_row_object_array(
      workbook.Sheets[firstSheet]
    );
    console.log("workbook", workbook);
    console.log("firstSheet", firstSheet);
    console.log("excel rows", excelRows.length);
    var table = document.createElement("table");
    table.id = "tableID";
    table.setAttribute("class", "table table-bordered table-stripped");
    if (excelRows.length >= 1) {
      $("button#btn-import").text("Import");
      $("button#btn-import").hide();

      rowCount = excelRows.length;
      console.log("Excel Data", excelRows);

      $("div#xlx-section").show();

      const columns = templateConfig;

      for (let index = 0; index < excelRows.length; index++) {
        const row = excelRows[index];
        const cols = Object.keys(row);

        console.log("row", row);
        console.log("cols", cols);
        console.log('cols.indexOf("postalcodes")', cols.indexOf("postalcodes"));

        if (cols.indexOf("postalcodes") === -1) {
          $("#imported-title").text("Invalid Excel data");
          errorsDiv.html(
            `<p> The data for postalcodes at row ${
              index + 1
            } was missing .Please enter the data and upload again...</p>`
          );
          return false;
        }
      }
      //load the data in table
      var postalCodes = [];
      $.map(excelRows, function (element, index) {
        var postalCode = element[columns[0]];
        postalCodes.push(postalCode);
      });
      var duplicatePostalCodes = {};
      postalCodes.forEach(function (x) {
        duplicatePostalCodes[x] = (duplicatePostalCodes[x] || 0) + 1;
      });
      let duplicatePostalCodesFound = 0;
      $.each(duplicatePostalCodes, function (i, v) {
        if (v > 1) {
          duplicatePostalCodesFound++;
        }
      });
      if (duplicatePostalCodesFound > 0) {
        showErrorsForExcel(
          "Errors!",
          "Duplicate postalcodes exists in the uploaded excel data. Make sure you provide an unique postal code.",
          true
        );
        return false;
      }

      var postalCodeRegex = /^[ABCEGHJKLMNPRSTVXY]\d[A-Z] \d[A-Z]\d$/i;

      let notValidateCount = 0;
      $.each(excelRows, function (index, value) {
        var postalCode = value[columns[0]];
        if (!postalCodeRegex.test(postalCode)) {
          notValidateCount++;
        }
      });

      if (notValidateCount > 0) {
        showErrorsForExcel(
          "Errors!",
          "Invalid postal code format. Please provide a valid Canadian postal code.",
          true
        );
        return false;
      }

      //console.log("Excel Columns", columns);
      var header = table.createTHead();
      // Create an empty <tr> element and add it to the first position of <thead>:
      var row = header.insertRow(0);
      var headerCell = document.createElement("TH");
      headerCell.innerHTML = "Sr no.";
      row.appendChild(headerCell);
      $.each(columns, function (index, value) {
        //console.log("Col Index", index, " Col Value", value);
        var headerCell = document.createElement("TH");
        headerCell.innerHTML = value;
        row.appendChild(headerCell);
      });

      var tbody = document.createElement("tbody");
      table.appendChild(tbody);
      //loop through each excel row
      for (var i = 0; i < excelRows.length; i++) {
        var participant = excelRows[i];

        if (!participant.hasOwnProperty("postalcodes")) {
          participant["postalcodes"] = "";
        }

        var modifiedString =
          participant["postalcodes"].substring(0, 3) +
          participant["postalcodes"].substring(3).replace(/\s/g, "");

        var tableRow = `
						<tr>
							<td>${i + 1}</td>
							<td>${modifiedString}</td>
						</tr>
					`;
        tbody.insertAdjacentHTML("beforeend", tableRow);
        excelData.push(participant);
      }
      showErrorsForExcel("Postal Code Data", "", false);
      excelDiv.innerHTML = "";
      excelDiv.appendChild(table);
    } else {
      $("button#btn-import").removeAttr("disabled");
      $("button#btn-import").text("Import");
      errorsDiv.html(
        `<p> The uploaded excel file either donot have data or doesnot match the template. Please download the excel template and fill the data properly in it...</p>`
      );
      return false;
    }
  }

  // Developer: Shreyas Mahamuni
  $(document).on("change", "#zipcode", function () {
    var filePath = $(this).val();
    var allowedExtensions = /(\.csv|\.xlsx|\.xls)$/i;
    if (filePath != "") {
      if (!allowedExtensions.exec(filePath)) {
        toastr.error(
          "This is an invalid excel data file. Make sure you select only that file which can be open in MS-Excel or Spreed Sheet or with extension xls or xlsx",
          "Invalid Excel File",
          {
            progressBar: false,
          }
        );
        $(this).val(null);
        return false;
      }
    }
  });

  $(document).on("click", "button#btn-upload", function (e) {
    e.preventDefault();
    var btn = $(this);
    var storeLocation = $("#storeLocation").val();
    console.log(storeLocation, "str");
    console.log(excelData, "exceldata");

    if (excelData.length === 0) {
      toastr.error("No data present to upload", "Failed", {
        progressBar: true,
        onHidden: function () {
          window.location.reload();
        },
      });
    } else {
      uploadJSON = [];
      excelData.forEach((element) => {
        console.log(element.postalcodes, "string");
        var modifiedString =
          element.postalcodes.substring(0, 3) +
          element.postalcodes.substring(3).replace(/\s/g, "");
        postalcodeObj = {
          postalcode: modifiedString,
          storeLocation: storeLocation,
        };
        uploadJSON.push(postalcodeObj);
      });

      $.ajax({
        type: "POST",
        url: baseUrl + "/zipcode/upload",
        contentType: "application/json",
        data: JSON.stringify(uploadJSON),
        dataType: "json",
        beforeSend: function () {
          btn.attr("disabled", true);
          btn.html(`Uploading Data. Please wait...`);
          $("div.preloader").show();
        },
        success: function (response) {
          $("div.preloader").hide();
          console.log(response);
          if (response.status === 200) {
            Swal.fire({
              icon: "success",
              title: "Success",
              text: response.msg,
              allowOutsideClick: false,
              keydownListenerCapture: false,
            }).then(() => {
              window.location.reload();
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Failed",
              text: response.msg,
              allowOutsideClick: false,
              keydownListenerCapture: false,
            }).then(() => {
              window.location.reload();
            });
          }
        },
        error: function (err) {
          console.log("AJAX ERROR", err);
          setTimeout(() => {
            $("div.preloader").hide();
            Swal.fire({
              icon: "error",
              title: "Failed",
              text: "Someting went wrong or server was unable to process all the data you uploaded. Please try again later.",
            }).then(() => {
              btn.removeAttr("disabled");
              btn.htmL(`Upload Data`);
            });
          }, 200);
        },
      });
    }
  });
});
