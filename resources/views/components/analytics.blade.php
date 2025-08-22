<div id="analytics-section">
    <div class="flex justify-between">
        <div class="flex flex-col items-center">
            <h3>Created </h3>
            <p>{{ \Dancycodes\Todopackage\Facades\AnalyticsFacades::getTotalCreated() }}</p>  
        </div>
        <div class="flex flex-col items-center">
            <h3>Completed </h3>
            <p>{{ \Dancycodes\Todopackage\Facades\AnalyticsFacades::getTotalCompleted() }}</p>
        </div>
        <div class="flex flex-col items-center">
            <h3>Pending</h3>
            <p>{{ \Dancycodes\Todopackage\Facades\AnalyticsFacades::getTotalUncompleted() }}</p>
        </div>
    </div>
</div>