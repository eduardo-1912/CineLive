package pt.ipleiria.estg.dei.amsi.cinelive.fragments;

import android.content.Intent;
import android.os.Bundle;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.activities.ConfiguracoesActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.CinemasListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.DataManager;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PreferencesManager;
import pt.ipleiria.estg.dei.amsi.cinelive.adapters.CinemasAdapter;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.FragmentCinemasBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Cinema;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ErrorUtils;

public class CinemasFragment extends Fragment {
    private FragmentCinemasBinding binding;
    private DataManager manager;
    private CinemasAdapter adapter;

    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // Obter o manager
        manager = DataManager.getInstance(getContext());
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentCinemasBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        // Configurar layout da recycler-view
        binding.rvCinemas.setLayoutManager(new LinearLayoutManager(getContext()));

        // Carregar cinemas
        loadCinemas();

        // Swipe refresh
        binding.swipeRefresh.setOnRefreshListener(() -> {
            binding.swipeRefresh.setRefreshing(false);

            // Apenas limpar a cache de cinemas se tiver internet
            if (ConnectionUtils.hasInternet(requireContext())) manager.clearCacheCinemas();

            // Carregar cinemas
            loadCinemas();
        });
    }

    private void loadCinemas() {
        binding.mainFlipper.setDisplayedChild(0); // Main Loading

        // Obter estado da ligação à internet
        boolean hasInternet = ConnectionUtils.hasInternet(requireContext());

        // Obter cinemas (API ou cache)
        manager.getCinemas(requireContext(), new CinemasListener() {
            @Override
            public void onSuccess(List<Cinema> cinemas) {
                setList(cinemas);

                // Tem cache mas não tem internet
                if (!hasInternet) ErrorUtils.showToast(requireContext(), ErrorUtils.Type.NO_INTERNET);
            }
            @Override
            public void onEmpty() {
                showError(ErrorUtils.Type.EMPTY_CINEMAS);
                manager.clearCacheFilmes();
            }
            @Override
            public void onError() {
                showError(hasInternet ? ErrorUtils.Type.API_ERROR : ErrorUtils.Type.NO_INTERNET);
            }
        });
    }

    private void setList(List<Cinema> cinemas) {
        // Evitar crash ao sair do fragment
        if (binding == null || !isAdded()) return;
        binding.mainFlipper.setDisplayedChild(2); // Main Content

        // Aceder às preferences
        PreferencesManager preferences = new PreferencesManager(requireContext());

        // Se clicou num cinema --> selecionar e guardar nas preferences
        adapter = new CinemasAdapter(cinemas, preferences.getCinemaId(), cinema -> {
            preferences.setCinemaId(cinema.getId());
            adapter.setCinemaSelecionado(cinema.getId());

            // Limpar cache de filmes
            manager.clearCacheFilmes();
        });

        binding.rvCinemas.setAdapter(adapter);
    }

    private void showError(ErrorUtils.Type type) {
        // Evitar crash ao sair do fragment
        if (binding == null || !isAdded()) return;
        binding.mainFlipper.setDisplayedChild(1); // Main Error
        ErrorUtils.showLayout(binding.mainError, type);

        // Action do botão
        binding.mainError.btnAction.setOnClickListener(v -> {
            switch (type) {
                case NO_INTERNET: case EMPTY_CINEMAS:
                    loadCinemas();
                    break;
                case API_ERROR:
                    startActivity(new Intent(requireContext(), ConfiguracoesActivity.class));
                    break;
            }
        });
    }

    @Override
    public void onResume() {
        super.onResume();

        // Carregar cinemas se não tiver cache
        if (manager.getCacheCinemas().isEmpty()) loadCinemas();
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}