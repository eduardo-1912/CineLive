package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.os.Bundle;
import android.view.View;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import org.json.JSONObject;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityConfiguracoesBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.ApiResponseListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PreferencesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ErrorUtils;

public class ConfiguracoesActivity extends AppCompatActivity {

    ActivityConfiguracoesBinding binding;
    PreferencesManager preferences;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivityConfiguracoesBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        // Aceder às preferences
        preferences = new PreferencesManager(this);

        // Testar ligação à API
        testApiConnection(preferences.getApiUrl(), ConnectionUtils.FAST_TIMEOUT);

        // Preencher campos e configurar listeners
        setFields();
        setOnClickListeners();
    }

    private void setFields() {
        binding.etApiHost.setText(preferences.getApiHost());
        binding.etApiPath.setText(preferences.getApiPath());
    }

    private void setOnClickListeners() {
        // Botão guardar
        binding.btnGuardar.setOnClickListener(v -> {
            // Obter valores
            String apiHost = String.valueOf(binding.etApiHost.getText());
            String apiPath = String.valueOf(binding.etApiPath.getText());

            // Testar ligação à API
            testApiConnection(apiHost + apiPath, ConnectionUtils.DEFAULT_TIMEOUT);

            // Guardar nas preferences
            preferences.setApiUrl(apiHost, apiPath);
        });

        // Botão restaurar
        binding.btnRestaurar.setOnClickListener(v -> {
            preferences.resetApiUrl();
            setFields();

            // Testar ligação à API
            testApiConnection(preferences.getApiUrl(), ConnectionUtils.DEFAULT_TIMEOUT);
        });
    }

    private void testApiConnection(String url, int timeout) {
        // Verificar ligação à internet
        if (!ConnectionUtils.hasInternet(this)) {
            ErrorUtils.showToast(this, ErrorUtils.Type.NO_INTERNET);
            return;
        }

        showLoading(true);

        ConnectionUtils.testApiConnection(this, url, timeout, new ApiResponseListener() {
            @Override
            public void onSuccess(String response) {
                showLoading(false);

                try {
                    // Mostrar resposta da API
                    binding.tvApiResponse.setText(new JSONObject(response).toString(3));
                    binding.tvApiResponse.setVisibility(View.VISIBLE);
                }
                catch (Exception e) {
                    ErrorUtils.showToast(getApplicationContext(), ErrorUtils.Type.API_ERROR);
                }
            }
            @Override
            public void onError() {
                showLoading(false);
                ErrorUtils.showToast(getApplicationContext(), ErrorUtils.Type.API_ERROR);
            }
        });
    }

    private void showLoading(boolean show) {
        binding.btnGuardar.setEnabled(!show);
        binding.btnRestaurar.setEnabled(!show);
        binding.etApiHost.setEnabled(!show);
        binding.etApiPath.setEnabled(!show);
        binding.progressBar.setVisibility(show ? View.VISIBLE : View.GONE);
        binding.tvApiResponse.setVisibility(View.GONE);
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}